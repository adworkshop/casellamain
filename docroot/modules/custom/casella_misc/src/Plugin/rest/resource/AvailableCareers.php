<?php

namespace Drupal\casella_misc\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a resource for database watchdog log entries.
 *
 * @RestResource(
 *   id = "casellaavailablecareers",
 *   label = @Translation("Casella available careers"),
 *   uri_paths = {
 *     "canonical" = "/api/careers/available.xml",
 *   }
 * )
 */
class AvailableCareers extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * Returns a list of recently added or updated careers.
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    $build = array(
      '#cache' => array(
        'max-age' => 0,
        'url.query_args',
      ),
    );

    $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

    // $careers = $nodeStorage->loadByProperties(['status' => TRUE, 'type' => 'job']);
    /*
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'job');
    $query->condition('status', '1');
    */
    $query = \Drupal::database()->select('node_field_data');
    $query->condition('node_field_data.type', 'job');
    $query->condition('node_field_data.status', '1');
    $query->fields('node_field_data', ['nid']);
    $results = $query->execute();
    $nids = [];
    while ($result = $results->fetchAssoc()) {
      $nids[] = $result['nid'];
    }

    $careers = $nodeStorage->loadMultiple($nids);

    $retVal = [
      'job' => [],
    ];

    $host = \Drupal::request()->getHost();
    $jobStorage = [];
    foreach ($careers as $career) {
      $jobStatus = $career->get('field_job_status')->getValue();
      $jobStatus = $termStorage->load($jobStatus[0]['target_id']);

      $locationTitle = $city = $state = FALSE;
      $location = $career->get('field_location')->getValue();

      $customLocation = $career->get('field_override_location')->getValue();

      if($customLocation[0]['value'] == "1") {
        $city = $career->get('field_override_location_value')->getValue();
        $state = $career->get('field_override_location_state')->getValue();
      }else if ($location) {
        $locationNode = $nodeStorage->load($location[0]['target_id']);

        if ($locationNode) {
          $locationTitle = $locationNode->get('title')->getValue();
          $city = $locationNode->get('field_city')->getValue();
          $state = $locationNode->get('field_state')->getValue();
        }
      }

      // Hiring managers not currently in use.
      // $hiringManagers = $career->get('field_hiring_managers')->getValue();
      // print_r($hiringManagers);

      $url = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $career->id());

      $title = $career->get('title')->getValue();
      $body = $career->get('body')->getValue();
      $created = $career->get('field_date_posted')->getValue();
      $createdStamp = isset($created[0]['value']) ? strtotime($created[0]['value']) : 0;

      // Index by the created stamp. Make it an array in case of overlap.
      $jobStorage[$createdStamp][] = [
        'ID' => $career->id(),
        'Title' => isset($title[0]['value']) ? $title[0]['value'] : '',
        'Ref' => 'JOB' . $career->id(),
        'Description' => isset($body[0]['value']) ? $body[0]['value'] : '',
        'Company' => $locationTitle && isset($locationTitle[0]['value']) ? $locationTitle[0]['value'] : '',
        'City' => $city && isset($city[0]['value']) ? $city[0]['value'] : '',
        'State' => $state && isset($state[0]['value']) ? $state[0]['value'] : '',
        'Contact' => 'Devin Siva',
        'Email' => 'devin.siva@Casella.com',
        'Created' => isset($created[0]['value']) ? date('F j, Y', strtotime($created[0]['value'])) : '',
        'Status' => $jobStatus->getName(),
        'JobLink' => '//' . $host . $url,
        'Published' => 'Yes',
      ];
    }

    // Reverse the order, we want most recent first.
    krsort($jobStorage);
    // Run through the Job Storage array and dump the jobs into the $retVal [].
    foreach ($jobStorage as $jobBag) {
      usort($jobBag, function($a, $b) {return strcmp($a["Title"], $b["Title"]);});
      $retVal['job'] = array_merge($retVal['job'], $jobBag);
    }

    $resource = new ResourceResponse($retVal);
    $resource->setLastModified();
    $resource->addCacheableDependency($build);
    $resource->setEtag();
    return $resource;
  }

}
