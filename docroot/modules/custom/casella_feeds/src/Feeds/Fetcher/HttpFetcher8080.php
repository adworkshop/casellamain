<?php

namespace Drupal\casella_feeds\Feeds\Fetcher;

use Drupal\feeds\Utility\Feed;
use Drupal\feeds\Feeds\Fetcher\HttpFetcher;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

/**
 * Defines an HTTP fetcher.
 *
 * @FeedsFetcher(
 *   id = "http8080",
 *   title = @Translation("Download Port 8080"),
 *   description = @Translation("Downloads data from a URL using Drupal's HTTP request handler via port 8080."),
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Fetcher\Form\HttpFetcherForm",
 *     "feed" = "Drupal\feeds\Feeds\Fetcher\Form\HttpFetcherFeedForm",
 *   },
 *   arguments = {"@http_client", "@cache.feeds_download", "@file_system"}
 * )
 */
class HttpFetcher8080 extends HttpFetcher {

  /**
   * Performs a GET request.
   *
   * @param string $url
   *   The URL to GET.
   * @param string $cache_key
   *   (optional) The cache key to find cached headers. Defaults to false.
   *
   * @return \Guzzle\Http\Message\Response
   *   A Guzzle response.
   *
   * @throws \RuntimeException
   *   Thrown if the GET request failed.
   */
  protected function get($url, $sink, $cache_key = FALSE) {
    $url = Feed::translateSchemes($url);

    $options = [RequestOptions::SINK => $sink];

    // Add cached headers if requested.
    if ($cache_key && ($cache = $this->cache->get($cache_key))) {
      if (isset($cache->data['etag'])) {
        $options[RequestOptions::HEADERS]['If-None-Match'] = $cache->data['etag'];
      }
      if (isset($cache->data['last-modified'])) {
        $options[RequestOptions::HEADERS]['If-Modified-Since'] = $cache->data['last-modified'];
      }
    }

    $options['config']['curl'] = ['CURLOPT_PORT' => 8080];

    try {
      $response = $this->client->get($url, $options);
    }
    catch (RequestException $e) {
      $args = ['%site' => $url, '%error' => $e->getMessage(), '%dump' => print_r($e, 1)];

      throw new \RuntimeException($this->t('The feed from %site seems to be broken because of error "%error". dump: %dump', $args));
    }

    if ($cache_key) {
      $this->cache->set($cache_key, array_change_key_case($response->getHeaders()));
    }

    return $response;
  }

}
