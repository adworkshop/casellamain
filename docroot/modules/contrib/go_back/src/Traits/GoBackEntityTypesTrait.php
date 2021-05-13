<?php

namespace Drupal\go_back\Traits;

use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Common entity types functionality.
 *
 * @package Drupal\go_back\Traits
 */
trait GoBackEntityTypesTrait {

  /**
   * The bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   */
  protected $bundleInfo;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Gets the current entity from url if it exists.
   *
   * @return array
   *   Returns an array that has the key as entity type and the value the entity
   *   object.
   */
  protected function getCurrentEntity() {
    $entityTypes = $this->getContentEntityTypes();
    $params = $this->routeMatch->getParameters()->all();
    if (!empty($params)) {
      foreach ($entityTypes as $type => $entityType) {
        // We loop through the valid entity types and search for the parameter
        // with the same name.
        if (isset($params[$type])) {
          return [$type => $params[$type]];
        }
      }
    }
    return [];
  }

  /**
   * Gets the bundles for the given type.
   *
   * @param string $type
   *   The content entity type definition if.
   *
   * @return array
   *   Returns an array of bundles if any, empty array otherwise.
   */
  protected function getContentEntityTypeBundles($type) {
    $result = [];

    $bundles = $this->getBundleInfo()->getBundleInfo($type);
    foreach ($bundles as $bundle => $info) {
      $result[$bundle] = $info['label'];
    }

    return $result;
  }

  /**
   * Gets the available content entity types for Go Back buttons.
   *
   * @return array
   *   Returns an array of available content entity types as type => label, that
   *   have a canonical link template. The array is sorted alphabetically by
   *   label.
   */
  protected function getContentEntityTypes() {
    $contentEntityTypes = [];
    $bundles = $this->getBundleInfo()->getAllBundleInfo();
    $definitions = $this->getEntityTypeManager()->getDefinitions();

    foreach ($definitions as $definition) {
      // We only want the content entity types that have a canonical link and
      // that are available in the list of all bundles.
      if (!$definition instanceof ContentEntityTypeInterface || !$definition->getLinkTemplate('canonical') || !isset($bundles[$definition->id()])) {
        continue;
      }
      $contentEntityTypes[$definition->id()] = $definition->getLabel();
    }
    asort($contentEntityTypes);
    return $contentEntityTypes;
  }

  /**
   * Gets the entity type bundle info service.
   *
   * @return \Drupal\Core\Entity\EntityTypeBundleInfo
   *   The entity type bundle info service.
   */
  protected function getBundleInfo() {
    if (!$this->bundleInfo) {
      $this->bundleInfo = \Drupal::service('entity_type.bundle.info');
    }

    return $this->bundleInfo;
  }

  /**
   * Gets the entity type manager service.
   *
   * @return \Drupal\Core\Entity\EntityTypeManager
   *   The entity type manager service.
   */
  protected function getEntityTypeManager() {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }

    return $this->entityTypeManager;
  }

}
