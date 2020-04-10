<?php

namespace Drupal\jsonapi_extras\ResourceType;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\ResourceType\ResourceTypeRepository;
use Drupal\jsonapi_extras\Plugin\ResourceFieldEnhancerManager;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Provides a repository of JSON API configurable resource types.
 */
class ConfigurableResourceTypeRepository extends ResourceTypeRepository {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Plugin manager for enhancers.
   *
   * @var \Drupal\jsonapi_extras\Plugin\ResourceFieldEnhancerManager
   */
  protected $enhancerManager;

  /**
   * The bundle manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleManager;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * A list of all resource types.
   *
   * @var \Drupal\jsonapi_extras\ResourceType\ConfigurableResourceType[]
   */
  protected $resourceTypes;

  /**
   * A list of only enabled resource types.
   *
   * @var \Drupal\jsonapi_extras\ResourceType\ConfigurableResourceType[]
   */
  protected $enabledResourceTypes;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_manager, EntityFieldManagerInterface $entity_field_manager, EntityRepositoryInterface $entity_repository, ResourceFieldEnhancerManager $enhancer_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($entity_type_manager, $bundle_manager, $entity_field_manager);
    $this->entityRepository = $entity_repository;
    $this->enhancerManager = $enhancer_manager;
    $this->configFactory = $config_factory;
    $this->entityFieldManager = $entity_field_manager;
    $this->bundleManager = $bundle_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    if (!$this->all) {
      $this->all = $this->getResourceTypes(FALSE);
    }
    return $this->all;
  }

  /**
   * {@inheritdoc}
   */
  public function get($entity_type_id, $bundle) {
    if (empty($entity_type_id)) {
      throw new PreconditionFailedHttpException('Server error. The current route is malformed.');
    }

    foreach ($this->getResourceTypes(FALSE) as $resource) {
      if ($resource->getEntityTypeId() == $entity_type_id && $resource->getBundle() == $bundle) {
        return $resource;
      }
    }

    return NULL;
  }

  /**
   * Returns an array of resource types.
   *
   * @param bool $include_disabled
   *   TRUE to included disabled resource types.
   *
   * @return array
   *   An array of resource types.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getResourceTypes($include_disabled = TRUE) {
    if (!isset($this->resourceTypes)) {
      $this->resourceTypes = [];
      foreach ($this->getEntityTypeBundleTuples() as $tuple) {
        list($entity_type_id, $bundle) = $tuple;
        $resource_config_id = sprintf('%s--%s', $entity_type_id, $bundle);
        $this->resourceTypes[] = new ConfigurableResourceType(
          $entity_type_id,
          $bundle,
          $this->entityTypeManager->getDefinition($entity_type_id)->getClass(),
          $this->getResourceConfig($resource_config_id),
          $this->enhancerManager,
          $this->configFactory
        );
      }
      foreach ($this->resourceTypes as $resource_type) {
        $relatable_resource_types = $this->calculateRelatableResourceTypes($resource_type);
        $resource_type->setRelatableResourceTypes($relatable_resource_types);
      }
    }

    if (!isset($this->enabledResourceTypes) && !$include_disabled) {
      $this->enabledResourceTypes = $this->filterOutDisabledResourceTypes(
        $this->resourceTypes,
        $this->getResourceConfigs()
      );
    }

    return ($include_disabled) ?
      $this->resourceTypes :
      $this->enabledResourceTypes;
  }

  /**
   * Get a single resource configuration entity by its ID.
   *
   * @param string $resource_config_id
   *   The configuration entity ID.
   *
   * @return \Drupal\jsonapi_extras\Entity\JsonapiResourceConfig
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getResourceConfig($resource_config_id) {
    $resource_configs = $this->getResourceConfigs();
    return isset($resource_configs[$resource_config_id]) ?
      $resource_configs[$resource_config_id] :
      new NullJsonapiResourceConfig([], '');
  }

  /**
   * Load all resource configuration entities.
   *
   * @return \Drupal\jsonapi_extras\Entity\JsonapiResourceConfig[]
   *   The resource config entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  function getResourceConfigs() {
    $resource_config_ids = [];
    foreach ($this->getEntityTypeBundleTuples() as $tuple) {
      list($entity_type_id, $bundle) = $tuple;
      $resource_config_ids[] = sprintf('%s--%s', $entity_type_id, $bundle);
    }
    /** @var \Drupal\jsonapi_extras\Entity\JsonapiResourceConfig[] $resource_configs */
    $resource_configs = $this->entityTypeManager
      ->getStorage('jsonapi_resource_config')
      ->loadMultiple($resource_config_ids);
    return $resource_configs;
  }

  /**
   * Takes a list of resource types and removes the disabled from it.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceType[] $resource_types
   *   The list of resource types including disabled ones.
   * @param \Drupal\Core\Entity\EntityInterface[] $resource_configs
   *   The configuration entities that accompany the resource types.
   *
   * @return \Drupal\jsonapi\ResourceType\ResourceType[]
   *   The list of enabled resource types.
   */
  protected function filterOutDisabledResourceTypes($resource_types, $resource_configs) {
    return array_filter($resource_types, function (ResourceType $resource_type) use ($resource_configs) {
      $resource_config_id = sprintf(
        '%s--%s',
        $resource_type->getEntityTypeId(),
        $resource_type->getBundle()
      );
      $resource_config = isset($resource_configs[$resource_config_id]) ? $resource_configs[$resource_config_id] : new NullJsonapiResourceConfig([], '');
      return !$resource_config->get('disabled');
    });
  }

  /**
   * Entity type ID and bundle iterator.
   *
   * @return array
   *   A list of entity type ID and bundle tuples.
   */
  protected function getEntityTypeBundleTuples() {
    $entity_type_ids = array_keys($this->entityTypeManager->getDefinitions());
    // For each entity type return as many tuples as bundles.
    return array_reduce($entity_type_ids, function ($carry, $entity_type_id) {
      $bundles = array_keys($this->bundleManager->getBundleInfo($entity_type_id));
      // Get all the tuples for the current entity type.
      $tuples = array_map(function ($bundle) use ($entity_type_id) {
        return [$entity_type_id, $bundle];
      }, $bundles);
      // Append the tuples to the aggregated list.
      return array_merge($carry, $tuples);
    }, []);
  }

}
