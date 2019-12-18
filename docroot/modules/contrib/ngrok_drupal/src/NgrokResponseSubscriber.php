<?php

namespace Drupal\ngrok_drupal;

use Drupal\Core\EventSubscriber\RedirectResponseSubscriber;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class NgrokResponseSubscriber.
 *
 * @package Drupal\ngrok
 */
class NgrokResponseSubscriber extends RedirectResponseSubscriber {

  /**
   * {@inheritdoc}
   */
  public function checkRedirectUrl(FilterResponseEvent $event) {
    $response = $event->getResponse();
    if ($response instanceof RedirectResponse) {

      $new_response = new TrustedRedirectResponse(
        $response->getTargetUrl(),
        $response->getStatusCode(),
        $response->headers->allPreserveCase()
      );

      $new_response->setProtocolVersion($response->getProtocolVersion());
      $new_response->setCharset($response->getCharset());

      foreach ($response->headers->getCookies() as $cookie) {
        $new_response->headers->setCookie($cookie);
      }
      $event->setResponse($new_response);
    }

    parent::checkRedirectUrl($event);
  }

}