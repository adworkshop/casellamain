<?php

namespace Drupal\casella_misc\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a resource for database watchdog log entries.
 *
 * @RestResource(
 *   id = "casellatownsbyzip",
 *   label = @Translation("Casella towns by zip"),
 *   uri_paths = {
 *     "canonical" = "/api/xml/towns/zip/{filter}",
 *    "http://casella.com/locationMarkers/api/xml/towns/zip/" = "/api/xml/towns/zip/"
 *   }
 * )
 */
class TownsByZipResource extends ResourceBase {

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
  public function get($zip = NULL) {
    if (is_null($zip)) {
      return new ResourceResponse();
    }

    if (!preg_match('/[0-9]{5}/', $zip)) {
      return new ResourceResponse();
    }

    $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

    $terms = $termStorage->loadByProperties(['field_zip_code' => $zip, 'vid' => 'towns_cities']);

    $retVal = [
      'town' => [],
    ];

    // need Zip, Name, Provider
    foreach ($terms as $term) {
      $temp = [
        'Zip' => $zip,
        'Name' => $term->getName(),
        'Provider' => '',
      ];
      $providers = $term->get('field_service_location');
      for ($i = 0; $i < $providers->count(); $i++) {
        $provider = $providers->get($i);
        $provider = $nodeStorage->load($provider->get('target_id')->getValue());

        $type = $provider->get('field_location_category')->getValue();

        if ('Hauling' == $type[0]['value']) {
          $temp['Provider'] = $provider->getTitle();
          break;
        }
      }

      if ('' != $temp['Provider']) {
        $retVal['town'][] = $temp;
      }
    }

    return new ResourceResponse($retVal);
  }

}
