<?php

namespace Drupal\ngrok_drupal;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\SessionConfiguration;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NgrokSessionConfiguration.
 *
 * @package Drupal\ngrok_drupal
 */
class NgrokSessionConfiguration extends SessionConfiguration {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * NgrokSessionConfiguration constructor.
   *
   * @param array $options
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct($options = [], ConfigFactoryInterface $config_factory = NULL) {
    parent::__construct($options);
    $this->configFactory = $config_factory;
  }

  /**
   * Return the session cookie domain.
   *
   * If the request is forwarded from ngrok.io use ngrok.io instead of
   * requested local domain.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Initial request.
   *
   * @return string
   *    the cookie domain
   */
  public function getCookieDomain(Request $request) {

    // Check if the headers contains a x-original-host value.
    if ($request->headers->has('x-original-host')) {
      $ngrok_domain = $this->configFactory->get('ngrok_drupal.settings')
        ->get('domain');
      $original_host = $request->headers->get('x-original-host');
      // If the original host comes from ngrok.io then use the ngrok.io domain
      // as cookie domain.
      if (!empty($ngrok_domain) && !empty($original_host)) {
        $ngrok_domain_length = strlen($ngrok_domain);
        $pos = strpos($original_host, $ngrok_domain, ($ngrok_domain_length * -1));
        if (FALSE !== $pos) {
          return '.' . $original_host;
        }
      }
    }

    return parent::getCookieDomain($request);
  }

}
