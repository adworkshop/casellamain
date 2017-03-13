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
   * Returns a list of markers in JSON format.
   *
   * @param string $town
   *   The town being searched for
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the log entry.
   */
  public function get() {
    $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

    $careers = $nodeStorage->loadByProperties(['status' => TRUE, 'type' => 'job']);

    $retVal = [
      'job' => '',
    ];

    $host = \Drupal::request()->getHost();
    foreach ($careers as $career) {
      $jobStatus = $career->get('field_job_status')->getValue();
      $jobStatus = $termStorage->load($jobStatus[0]['target_id']);

      $location = $career->get('field_location')->getValue();
      $location = $nodeStorage->load($location[0]['target_id']);

      $locationTitle = $location->get('title')->getValue();
      $city = $location->get('field_city')->getValue();
      $state = $location->get('field_state')->getValue();

      // Hiring managers not currently in use.
      // $hiringManagers = $career->get('field_hiring_managers')->getValue();
      // print_r($hiringManagers);

      $url = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $career->id());

      $title = $career->get('title')->getValue();
      $body = $career->get('body')->getValue();
      $created = $career->get('field_date_posted')->getValue();

      $retVal['job'][] = [
        'ID' => $career->id(),
        'Title' => isset($title[0]['value']) ? $title[0]['value'] : '',
        'Ref' => 'JOB' . $career->id(),
        'Description' => isset($body[0]['value']) ? $body[0]['value'] : '',
        'Company' => isset($locationTitle[0]['value']) ? $locationTitle[0]['value'] : '',
        'City' => isset($city[0]['value']) ? $city[0]['value'] : '',
        'State' => isset($state[0]['value']) ? $state[0]['value'] : '',
        'Contact' => 'Devin Siva',
        'Email' => 'devin.siva@Casella.com',
        'Created' => isset($created[0]['value']) ? date('F j, Y', strtotime($created[0]['value'])) : '',
        'Status' => $jobStatus->getName(),
        'JobLink' => '//' . $host . $url,
        'Published' => 'Yes',
      ];
    }

    return new ResourceResponse($retVal);
  }

}
