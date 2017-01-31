<?php

/**
 * @file
 * Contains \Drupal\node\Tests\NodeAccessJoinTest.
 */

namespace Drupal\node\Tests;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\NodeType;
use Drupal\views\Tests\ViewTestData;

/**
 * Tests Node Access on join.
 *
 * @group views
 */
class NodeAccessJoinTest extends NodeTestBase {

  /**
   * The installation profile to use with this test.
   *
   * @var string
   */
  protected $profile = 'standard';

  /**
   * The user that will create the articles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authorUser;

  /**
   * A user with just access content permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $regularUser;

  /**
   * A user with access to private articles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $accessUser;

  /**
   * Articles.
   *
   * @var array
   */
  protected $articles;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node_access_test', 'views', 'node_test_views'];

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_node_access_join'];

  protected function setUp() {
    parent::setUp();

    node_access_test_add_field(NodeType::load('article'));

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'related_article',
      'entity_type' => 'node',
      'translatable' => FALSE,
      'entity_types' => [],
      'settings' => [
        'target_type' => 'node',
      ],
      'type' => 'entity_reference',
    ]);
    $field_storage->save();
    $field = FieldConfig::create([
      'field_name' => 'related_article',
      'entity_type' => 'node',
      'bundle' => 'page',
      'label' => 'Related Article',
      'settings' => [
        'handler' => 'default',
        'handler_settings' => [
          // Reference a single vocabulary.
          'target_bundles' => [
            'article',
          ],
        ],
      ],
    ]);
    $field->save();

    entity_get_display('node', 'page', 'default')
      ->setComponent('related_article')
      ->save();
    entity_get_form_display('node', 'page', 'default')
      ->setComponent('related_article', [
        'type' => 'entity_reference_autocomplete',
      ])
      ->save();

    $field = FieldConfig::create([
      'field_name' => 'related_article',
      'entity_type' => 'node',
      'bundle' => 'article',
      'label' => 'Related Article',
      'settings' => [
        'handler' => 'default',
        'handler_settings' => [
          // Reference a single vocabulary.
          'target_bundles' => [
            'article',
          ],
        ],
      ],
    ]);
    $field->save();

    entity_get_display('node', 'article', 'default')
      ->setComponent('related_article')
      ->save();
    entity_get_form_display('node', 'article', 'default')
      ->setComponent('related_article', [
        'type' => 'entity_reference_autocomplete',
      ])
      ->save();

    node_access_rebuild();
    \Drupal::state()->set('node_access_test.private', TRUE);
  }

  /**
   * Tests the accessibility of joined nodes.
   *
   * - Create two users with "access content" and "create article" permissions
   *   who can each access their own private articles but not others'.
   * - Create article-type nodes with and without references to other articles.
   *   The articles and references represent all possible combinations of the
   *   tested access types.
   * - Create page-type nodes referencing each of the articles, as well as a
   *   page with no reference.
   * - Use a custom view that creates two joins between nodes and has a
   *   node_access tag. The view lists the page nodes, the article
   *   referenced by each page node, and the article referenced by each
   *   article.
   *
   * - Login with the author user and check that he does not have access to
   *   private nodes created by other users. Test access using total row
   *   count as well as checking for presence of individual page titles.
   * - Repeat tests using a user with only the "access content" permission,
   *   confirming this user does not have access to any private nodes.
   * - Repeat tests using a user with "access content" and "node test view"
   *   permissions, confirming this user sees the complete view.
   */
  public function testNodeAccessJoin() {

    // User to add articles and test author access.
    $this->authorUser = $this->drupalCreateUser(['access content', 'create article content']);
    // Another user to add articles (whose private articles can not be accessed
    // by authorUser).
    $this->otherUser = $this->drupalCreateUser(array('access content', 'create article content'));

    // Create the articles.

    // The articles are stored in an array keyed by $article and $reference2, where
    // $article is the access type of the article itself, and $reference2 is the
    // access type of the reference linked to by the article.
    //  'public' articles are created by otherUser with private=0.
    //  'private' articles are created by otherUser with private=1.
    //  'author_public' articles are created by authorUser with private=0.
    //  'author_private' articles are created by authorUser with private=1.
    //  'no_reference' is used for references when there is no related article.
    foreach (['no_reference', 'public', 'private', 'author_public', 'author_private'] as $reference2) {
      foreach (['public', 'private', 'author_public', 'author_private'] as $article) {
//        // Enable this check to simulate tests in patch#299
//        if ($reference2 != 'no_reference' && ((substr($article, 0, 6) == 'author') != (substr($reference2, 0, 6) == 'author'))) {
//          continue;
//        }
//        // Enable this check to simulate tests in patch#302
//        if ($reference2 != 'no_reference' && ((substr($article, 0, 6) == 'author') != (substr($reference2, 0, 6) == 'author')) && !(substr($article, -7) == 'private' && substr($reference2, -7) == 'private')) {
//          continue;
//        }

        $is_author = (substr($article, 0, 6) == 'author');
        $is_private = (substr($article, -7) == 'private');
        $edit = [
          'type' => 'article',
          'uid' => $is_author ? $this->authorUser->id() : $this->otherUser->id(),
        ];
        $edit['private'][0]['value'] = $is_private;
        // The article names provide the access status of the article and the
        // access status of the related article (if any).
        // The naming system ensures that the text 'Article $article' will only appear
        // in the view if an article with that access type is displayed in the view. (The text
        // '$article' alone will appear in the titles of other nodes that reference
        // an article.)
        $edit['title'] = "Article $article - $reference2";
        if ($reference2 != 'no_reference') {
          $edit['related_article'][0]['target_id'] = $this->articles[$reference2]['no_reference'];
        }
        $node = $this->drupalCreateNode($edit);
        $this->articles[$article][$reference2] = $node->id();

        $this->assertEqual((int) $is_private, (int) $node->private->value, 'The private status of the article node was properly set in the node_access_test table.' . $node->uid->target_id);
        if ($reference2 != 'no_reference') {
          $this->assertEqual((int) $this->articles[$reference2]['no_reference'], (int) $node->related_article->target_id, 'Proper article attached to article.');
        }
      }
    }

    // Add a blank 'no_reference' entry to the article list, so that a page with
    // no reference gets created.
    $this->articles['no_reference']['no_reference'] = NULL;

    $total = 0;
    $count_s_total = $count_s2_total = 0;
    $count_s_public = $count_s2_public = 0;
    $count_s_author = $count_s2_author = 0;
    $total_public = $total_author = 0;

    // Create page nodes referencing each article, as well as a page with no reference.
    foreach ($this->articles as $reference => $list) {
      foreach ($list as $reference2 => $article_nid) {
        $title = "Page - $reference";
        if ($reference != 'no_reference') {
          $title .= " - $reference2";
        }
        $edit = [
          'type' => 'page',
          'title' => $title,
        ];
        if ($article_nid) {
          $edit['related_article'][0]['target_id'] = $article_nid;
        }
        $node = $this->drupalCreateNode($edit);
        if ($article_nid) {
          $this->assertEqual((int) $article_nid, (int) $node->related_article->target_id, 'Proper article attached to page.');
        }

        // Calculate totals expected for each user type
        // Total number of pages.
        $total++;
        // Total number of primary and secondary references.
        if ($reference != 'no_reference') {
          $count_s_total++;
          if ($reference2 != 'no_reference') {
            $count_s2_total++;
          }
        }
        // Public users only see 'public' and 'author_public' articles.
        if (substr($reference, -6) == 'public') {
          $count_s_public++;
          if (substr($reference2, -6) == 'public') {
            $count_s2_public++;
          }
        }
        // authorUser sees 'public', 'author_public', and 'author_private' articles.
        if (substr($reference, -6) == 'public' || substr($reference, 0, 6) == 'author') {
          $count_s_author++;
          if (substr($reference2, -6) == 'public' || substr($reference2, 0, 6) == 'author') {
            $count_s2_author++;
          }
        }

        // $total_public and $total_author are not currently in use -- but
        // represent the totals when joins are handled by adding an is-null
        // check (i.e., if inaccessible references caused the entire row to be
        // be hidden from view, instead of hiding just one cell of the table).
        // Count of pages where all related articles are accessible by
        // public users.
        if (substr($reference, -7) != 'private' && substr($reference2, -7) != 'private') {
          $total_public++;
        }
        // Count of pages where all related articles are accessible by
        // authorUser.
        if ($reference != 'private' && $reference2 != 'private') {
          $total_author++;
        }
      }
    }

    // Generate a view listing all the pages, and check the view's content for
    // users with three different access levels.

    ViewTestData::createTestViews(get_class($this), ['node_test_views']);

    // Check the author of the 'author' articles.
    $this->drupalLogin($this->authorUser);
    $this->drupalGet('test-node-access-join');
    $chk_total = count($this->xpath("//td[@headers='view-title-table-column']"));
    $this->assertEqual($chk_total, $total, 'Author should see ' . $total . ' rows. Actual: ' . $chk_total);
    $chk_total = count($this->xpath("//td[@headers='view-title-1-table-column']/a"));
    $this->assertEqual($chk_total, $count_s_author, 'Author should see ' . $count_s_author . ' primary references. Actual: ' . $chk_total);
    $chk_total = count($this->xpath("//td[@headers='view-title-2-table-column']/a"));
    $this->assertEqual($chk_total, $count_s2_author, 'Author should see ' . $count_s2_author . ' secondary references. Actual: ' . $chk_total);
    $this->assertText('Page - no_reference', 'Author should see page with no reference.');
    $this->assertText('Page - public - no_reference', 'Author should see page with one public reference.');
    $this->assertText('Page - public - public', 'Author should see page with two public references.');
    $this->assertText('Page - author_private - no_reference', 'Author should see page with own private reference.');
    $this->assertText('Article public', 'Author should see articles with access type: public.');
    $this->assertNoText('Article private', 'Author should not see articles with access type: private.');
    $this->assertText('Article author_public', 'Author should see articles with access type: author_public.');
    $this->assertText('Article author_private', 'Author should see articles with access type: author_private.');
    // Following test is no longer relevant.
    //$this->assertNoText('- private', 'Author should not see pages related to others\' private articles.');

    // Check a regular user (who did not author any articles).
    $this->regularUser = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($this->regularUser);
    $this->drupalGet('test-node-access-join');
    $chk_total = count($this->xpath("//td[@headers='view-title-table-column']"));
    $this->assertEqual($chk_total, $total, 'Public user should see ' . $total . ' rows. Actual: ' . $chk_total);
    $chk_total = count($this->xpath("//td[@headers='view-title-1-table-column']/a"));
    $this->assertEqual($chk_total, $count_s_public, 'Public user should see ' . $count_s_public . ' primary references. Actual: ' . $chk_total);
    $chk_total = count($this->xpath("//td[@headers='view-title-2-table-column']/a"));
    $this->assertEqual($chk_total, $count_s2_public, 'Public user should see ' . $count_s2_public . ' secondary references. Actual: ' . $chk_total);
    $this->assertText('Page - no_reference', 'Public user should see page with no reference.');
    $this->assertText('Page - public - no_reference', 'Public user should see page with one public reference.');
    $this->assertText('Page - public - public', 'Public user should see page with two public references.');
    $this->assertText('Article public', 'Public user should see articles with access type: public.');
    $this->assertNoText('Article private', 'Public user should not see articles with access type: private.');
    $this->assertText('Article author_public', 'Public user should see articles with access type: author_public.');
    $this->assertNoText('Article author_private', 'Public user should not see articles with access type: author_private.');
    // Following test is no longer relevant.
    //$this->assertNoText('private', 'Public user should not see pages related to any private articles.');

    // Check a user with the special 'node test view' permission, who should
    // be able to view all pages and articles.
    $this->accessUser = $this->drupalCreateUser(['access content', 'node test view']);
    $this->drupalLogin($this->accessUser);
    $this->drupalGet('test-node-access-join');
    $chk_total = count($this->xpath("//td[@headers='view-title-table-column']"));
    $this->assertEqual($chk_total, $total, 'Full-access user should see ' . $total . ' rows. Actual: ' . $chk_total);
    $chk_total = count($this->xpath("//td[@headers='view-title-1-table-column']/a"));
    $this->assertEqual($chk_total, $count_s_total, 'Full-access user should see ' . $count_s_total . ' primary references. Actual: ' . $chk_total);
    $chk_total = count($this->xpath("//td[@headers='view-title-2-table-column']/a"));
    $this->assertEqual($chk_total, $count_s2_total, 'Full-access user should see ' . $count_s2_total . ' secondary references. Actual: ' . $chk_total);
    $this->assertText('Page - no_reference', 'Full-access user should see page with no reference.');
    $this->assertText('Page - public - no_reference', 'Full-access user should see page with one public reference.');
    $this->assertText('Page - public - public', 'Full-access user should see page with two public references.');
    $this->assertText('Page - author_private - no_reference', 'Full-access user should see page with author_private reference.');
    $this->assertText('Article public', 'Full-access user should see articles with access type: public.');
    $this->assertText('Article private', 'Full-access user should see articles with access type: private.');
    $this->assertText('Article author_public', 'Full-access user should see articles with access type: author_public.');
    $this->assertText('Article author_private', 'Full-access user should see articles with access type: author_private.');
  }
}
