<?php

namespace Drupal\casella_misc\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a resource for database watchdog log entries.
 *
 * @RestResource(
 *   id = "casellalocationmarkers",
 *   label = @Translation("Casella location markers"),
 *   uri_paths = {
 *     "canonical" = "/locationMarkers/{filter}",
 *     "http://casella.com/locationMarkers" = "/locationMarkers/all"
 *   }
 * )
 */
class LocationMarkersResource extends ResourceBase {
  /**
   * @var array
   */
  private $locationCategoryMap;

  /**
   * Responds to GET requests.
   *
   * Returns a list of markers in JSON format.
   *
   * @param string $town
   *   The town being searched for
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the log entry.
   */
  public function get($town = NULL) {
    if (!$this->locationCategoryMap) {
      $settings = \Drupal\field\Entity\FieldStorageConfig::loadByName('node', 'field_location_category');
      $settings = $settings->getSetting('allowed_values');
      if ($settings) {
        $this->locationCategoryMap = $settings;
      }
    }

    $retVal = array();

    $view = \Drupal\views\Views::getView('locations');
    $view->setDisplay('block_4');

    $response = $view->execute();
    if (!$response) {
      return new ResourceResponse($retVal);
    }

    $retVal['townInfo'] = $this->getTownInfo($town);

    $retVal['markers'] = array();

    $alias_manager = \Drupal::service('path.alias_manager');

    // Strip the information out of the _entity included with the view row.
    foreach ($view->result as $viewRow) {
      $values = $viewRow->_entity->toArray();
      if (!isset($values['field_latitude'][0]['value']) || !isset($values['field_longitude'][0]['value'])) {
        continue;
      }

      $locationCategory = isset($this->locationCategoryMap[$values['field_location_category'][0]['value']]) ? $this->locationCategoryMap[$values['field_location_category'][0]['value']] : $values['field_location_category'][0]['value'];
      $retVal['markers'][] = array(
        'nid' => $viewRow->_entity->id(),
        'path' => $alias_manager->getAliasByPath('/node/' . $viewRow->_entity->id()),
        'title' => $values['title'][0]['value'],
        'latitude' => $values['field_latitude'][0]['value'],
        'longitude' => $values['field_longitude'][0]['value'],
        'type' => $locationCategory,
        'typeLC' => strtolower($values['field_location_category'][0]['value']),
      );
    }

    return new ResourceResponse($retVal);
  }

  /**
   * Attempts to load then parse the requested town term.
   *
   * @param null|string $town
   * @return array
   */
  private function getTownInfo($town = NULL) {
    if (NULL == $town) {
      return array();
    }

    $town = preg_replace('/^"|"$/', '', $town);

    // load the term.
    $term = taxonomy_term_load_multiple_by_name($town, 'towns_cities');
    if (!count($term)) {
      return array();
    }
    $term = array_pop($term);

    // snag the lat/lon pair.
    $lat = $term->get('field_latitude')->getValue();
    $lon = $term->get('field_longitude')->getValue();
    if (!$lat || !$lon) {
      return array();
    }

    // Send it back with the markers.
    return array(
      'latitude' => $lat[0]['value'],
      'longitude' => $lon[0]['value'],
      'townName' => $term->getName(),
    );
  }

}
