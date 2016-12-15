<?php

namespace Drupal\casella_feeds\Plugin\aggregator\fetcher;

use Drupal\aggregator\Plugin\FetcherInterface;
use Drupal\aggregator\FeedInterface;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\aggregator\Plugin\aggregator\fetcher\DefaultFetcher;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a default fetcher implementation.
 *
 * Uses the http_client service to download the feed.
 *
 * @AggregatorFetcher(
 *   id = "casella_aggregator",
 *   title = @Translation("Casella fetcher"),
 *   description = @Translation("Downloads data from a URL using Drupal's HTTP request handler. Adds a user agent header.")
 * )
 */
class CasellaFetcher extends DefaultFetcher implements FetcherInterface, ContainerFactoryPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function fetch(FeedInterface $feed) {
    $request = new Request('GET', $feed->getUrl());
    $feed->source_string = FALSE;

    // Generate conditional GET headers.
    if ($feed->getEtag()) {
      $request = $request->withAddedHeader('If-None-Match', $feed->getEtag());
    }
    if ($feed->getLastModified()) {
      $request = $request->withAddedHeader('If-Modified-Since', gmdate(DateTimePlus::RFC7231, $feed->getLastModified()));
    }

    $request = $request->withAddedHeader('user-agent', "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36");

    try {

      /** @var \Psr\Http\Message\UriInterface $actual_uri */
      $actual_uri = NULL;
      $response = $this->httpClientFactory->fromOptions(['allow_redirects' => [
        'on_redirect' => function(RequestInterface $request, ResponseInterface $response, UriInterface $uri) use (&$actual_uri) {
          $actual_uri = (string) $uri;
        }
      ]])->send($request);

      // In case of a 304 Not Modified, there is no new content, so return
      // FALSE.
      if ($response->getStatusCode() == 304) {
        return FALSE;
      }

      $feed->source_string = (string) $response->getBody();
      if ($response->hasHeader('ETag')) {
        $feed->setEtag($response->getHeaderLine('ETag'));
      }
      if ($response->hasHeader('Last-Modified')) {
        $feed->setLastModified(strtotime($response->getHeaderLine('Last-Modified')));
      }
      $feed->http_headers = $response->getHeaders();

      // Update the feed URL in case of a 301 redirect.
      if ($actual_uri && $actual_uri !== $feed->getUrl()) {
        $feed->setUrl($actual_uri);
      }
      return TRUE;
    }
    catch (RequestException $e) {
      $this->logger->warning('The feed from %site seems to be broken because of error "%error".', array('%site' => $feed->label(), '%error' => $e->getMessage()));
      drupal_set_message(t('The feed from %site seems to be broken because of error "%error".', array('%site' => $feed->label(), '%error' => $e->getMessage())), 'warning');
      return FALSE;
    }
  }

}
