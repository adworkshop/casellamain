<?php

/**
 * @file
 * Contains \Drupal\adw_search\EventSubscriber\AdwSearchSubscriber.
 */

namespace Drupal\casella_misc\Service;

use \Drupal\file\Entity\File;
use \Drupal\image\Entity\ImageStyle;


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

    // Earthlife no longer uses the subsection landing page. Hardcoding the
    // destination as the Products landing page.
    if ('earthlife' == $subsection) {
      return $this->aliasManager->getAliasByPath('/node/26');
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
   *
   * @param $parentNid
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

    return '';
  }

  /**
   * Find and return the content's navigation parent's logo, if one exists.
   * @param array $parentNids
   * @return array
   */
  public function findParentsLogo($parentNids) {
    if (!count($parentNids)) {
      return array();
    }

    foreach ($parentNids as $parentNid) {
      $logoFid = $this->findParentLogo($parentNid);
      if ($logoFid != '') {
        $path = $this->loadLogoPath($logoFid);
        if ($path != '') {
          $parentPath = $this->aliasManager->getAliasByPath('/node/' . $parentNid);
          return array(
            'path' => $parentPath,
            'logo' => $path,
          );
        }
      }
    }

    return array();
  }

  /**
   * Checks the db to see if the nid is associated with a logo.
   *
   * @param $parentNid
   * @return string
   */
  private function findParentLogo($parentNid) {
    $select = $this->dbConnection->select('node__field_logo', 'field_logo');
    $select->fields('field_logo', array('field_logo_target_id'));
    $select->condition('entity_id', $parentNid, '=');
    $select->condition('bundle', 'page', '=');

    $data = $select->execute();
    $results = $data->fetchAll(\PDO::FETCH_OBJ);
    if (!count($results)) {
      return '';
    }

    foreach ($results as $result) {
      return $result->field_logo_target_id;
    }
  }

  /**
   * Loads the file by file ID and returns the uri or a blank screen.
   *
   * @param $logoFid
   * @return string
   */
  private function loadLogoPath($logoFid) {
    $file = File::load($logoFid);
    if (!$file) {
      return '';

    }
    $style = ImageStyle::load('max_400');
    return $style->buildUrl($file->getFileUri());
  }

  /**
   * Return an array of the content's parents in the main nav.
   *
   * @param array $activeTrail
   * @return array
   */
  public function findParentIds($activeTrail) {
    // Strip off the empty element at the end.
    $keys = array_keys($activeTrail);
    if (array_pop($keys) == '') {
      array_pop($activeTrail);
    }
    // And current element.
    array_shift($activeTrail);

    if (!count($activeTrail)) {
      return array();
    }

    $select = $this->dbConnection->select('menu_tree', 'menu_tree');
    $select->fields('menu_tree', array('route_param_key'));
    $select->condition('id', $activeTrail, 'IN');
    $select->condition('menu_name', 'main', '=');
    $select->orderBy('depth', 'DESC');

    $data = $select->execute();
    $results = $data->fetchAll(\PDO::FETCH_OBJ);
    $parents = array();
    foreach ($results as $result) {
      if (substr($result->route_param_key, 0, 5) == 'node=') {
        $parents[] = substr($result->route_param_key, 5);
      }
    }

    return $parents;
  }
}
