<?php
/**
 * @file
 * Contains \Drupal\casella_blog\Plugin\Block\CasellaBlogArchive.
 */

namespace Drupal\casella_blog\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**node
 * Provides a blog archive block.
 *
 * @Block(
 *   id = "casella_blog_archive",
 *   admin_label = @Translation("Casella Blog Archive"),
 *   category = @Translation("Casella")
 * )
 */
class CasellaBlogArchive extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the dates by year and month.
    $config = $this->getConfiguration();
    // The format of the date depends on the show_months setting.
    $format = 1 === $config['show_months'] ? '%Y %m %M' : '%Y';

    $dates = \Drupal::database()->query('
      SELECT DISTINCT DATE_FORMAT(date_posted.field_date_posted_value, :format) AS date
      FROM {node__field_date_posted} date_posted
      INNER JOIN {node_field_data} node_field_data ON node_field_data.nid = date_posted.entity_id
      WHERE date_posted.bundle = :bundle
        AND node_field_data.status = 1
      ORDER BY date ASC
    ', [':format' => $format, ':bundle' => 'blog_post']);

    $dateArray = [];
    while ($date = $dates->fetchAssoc()) {
      $dateBits = explode(' ', $date['date']);
      if (count($dateBits) > 1) {
        $dateArray[$dateBits[0]][$dateBits[1]] = $dateBits[2];
      }
      else {
        $dateArray[$dateBits[0]] = [];
      }
    }

    $markup = '';
    foreach ($dateArray as $year => $bits) {
      $yearLink = Url::fromUri('internal:/blog/archive/' . $year);
      $yearMarkup = Link::fromTextAndUrl($year, $yearLink)->toString();

      if (count($bits)) {
        $yearMarkup .= '<ul class="archive-links__inner">';
        foreach ($bits as $numMonth => $stringMonth) {
          $monthLink = Url::fromUri('internal:/blog/archive/' . $year . '/' . $numMonth);
          $yearMarkup .= '<li>' . Link::fromTextAndUrl($this->t($stringMonth), $monthLink)->toString() . '</li>';
        }
        $yearMarkup .= '</ul>';
      }
      $markup .= '<li>' . $yearMarkup . '</li>';
    }

    if ('' != $markup) {
      $markup = '<ul class="archive-links">' . $markup . '</ul>';
    }

    return [
      '#markup' => $markup,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['show_months'] = [
      '#default_value' => $this->configuration['show_months'],
      '#type' => 'checkbox',
      '#title' => $this->t('Show Months'),
      '#options' => [0 => "Don't show months", 1 => "Show months"],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['show_months'] = $form_state->getValue('show_months');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'show_months' => 0,
    ];
  }
}
