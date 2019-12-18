<?php

namespace Drupal\ngrok_drupal;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Class NgrokServiceProvider.
 *
 * Override the session_configuration service.
 *
 * @package Drupal\ngrok
 */
class NgrokDrupalServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('session_configuration')) {
      // Load the definition of the session_configuration servie.
      $definition = $container->getDefinition('session_configuration');
      // Override the default class, by our own.
      $definition->setClass(
        '\Drupal\ngrok_drupal\NgrokSessionConfiguration'
      );
    }
    if ($container->hasDefinition('redirect_response_subscriber')) {
      $definition = $container->getDefinition('redirect_response_subscriber');
      $definition->setClass(
        '\Drupal\ngrok_drupal\NgrokResponseSubscriber'
      );
    }
  }

}
