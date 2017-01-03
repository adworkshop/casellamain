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

  public function findParentsSection(array $parentNids) {
    if (!count($parentNids)) {
      return '';
    }

    foreach ($parentNids as $parentNid) {
      $subsection = $this->findParentSection($parentNid);
      if ($subsection != '') {
        return $subsection;
      }
    }

    return '';
  }

  /**
   * Finds the parents section and returns the string value.
   * @param $nid
   * @return string
   */
  private function findParentSection($parentNid) {
    $select = $this->dbConnection->select('node__field_subsection', 'field_subsection');
    $select->fields('field_subsection', array('field_subsection_value'));
    $select->condition('entity_id', $parentNid, '=');
    $select->condition('bundle', 'page', '=');

    $data = $select->execute();
    $results = $data->fetchAll(\PDO::FETCH_OBJ);
    if (!count($results)) {
      return '';
    }

    foreach ($results as $result) {
      return $result->field_subsection_value;
    }
  }
}
