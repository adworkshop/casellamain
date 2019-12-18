<?php

namespace Drupal\ngrok_drupal;

use Drupal\Core\Session\SessionConfiguration;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NgrokSessionConfiguration.
 *
 * @package Drupal\ngrok_drupal
 */
class NgrokSessionConfiguration extends SessionConfiguration {

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
      $originalHost = $request->headers->get('x-original-host');
      // If the original host comes from ngrok.io then use the ngrok.io domain
      // as cookie domain.
      if (preg_match('/.*\.ngrok\.io$/', $originalHost) === 1) {
        return '.' . $originalHost;
      }
    }

    return parent::getCookieDomain($request);
  }

}
