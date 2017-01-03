<?php

/**
 * @file
 * Contains \Drupal\adw_search\EventSubscriber\AdwSearchSubscriber.
 */

namespace Drupal\casella_misc\Service;


class CasellaMiscService  {
  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $dbConnection;

  /**
   * @var \Drupal\Core\Path\AliasManager
   */
  private $aliasManager;

  /**
   * CasellaMiscService constructor.
   * @param \Drupal\Core\Path\AliasManager $aliasManager
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct($aliasManager, $database) {
    $this->aliasManager = $aliasManager;
    $this->dbConnection = $database;
  }

  /**
   * Pulls the current subsection homepage's path.
   * @param $subsection
   * @return string
   */
  public function getSubsectionPath($subsection) {
    if (!$subsection) {
      return '';
    }

    $subsectionNidSelect = $this->dbConnection->select('node__field_subsection', 'subsection');
    $subsectionNidSelect->fields('subsection', array('entity_id'));
    $subsectionNidSelect->condition('bundle', 'subsection_homepage', '=');
    $subsectionNidSelect->condition('field_subsection_value', $subsection, '=');

    $data = $subsectionNidSelect->execute();
    $results = $data->fetchAll(\PDO::FETCH_OBJ);

    // Ensure we found something, and that we aren't already on the homepage.
    if ($results) {
      return $this->aliasManager->getAliasByPath('/node/' . $results[0]->entity_id);
    }

    return '';
  }
}
