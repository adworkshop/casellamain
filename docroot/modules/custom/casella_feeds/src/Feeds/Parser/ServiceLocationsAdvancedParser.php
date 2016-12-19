<?php

namespace Drupal\casella_feeds\Feeds\Parser;

use Drupal\casella_feeds\Feeds\Item\ServiceLocationItemAdvanced;
use Drupal\feeds\Component\XmlParserTrait;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;

/**
 * Defines a SitemapXML feed parser.
 *
 * @FeedsParser(
 *   id = "casella_service_locations_advanced",
 *   title = @Translation("Casella Advanced Service Locations XML"),
 *   description = @Translation("Parse Service Locations XML format feeds, with additional field handling.")
 * )
 */
class ServiceLocationsAdvancedParser extends PluginBase implements ParserInterface {
  use XmlParserTrait;

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $raw = trim($fetcher_result->getRaw());
    if (!strlen($raw)) {
      throw new EmptyFeedException();
    }

    // Yes, using a DOM parser is a bit inefficient, but will do for now.
    // @todo XML error handling.
    $this->startXmlErrorHandling();
    $xml = new \SimpleXMLElement($raw);
    $this->stopXmlErrorHandling();
    $result = new ParserResult();

    foreach ($xml->location as $location) {
      $item = new ServiceLocationItemAdvanced();

      $item->set('guid', $this->casella_feeds_get_xml_element_value($location, 'ID'));
      $item->set('title', $this->casella_feeds_get_xml_element_value($location, 'Title'));
      $item->set('type', $this->casella_feeds_get_xml_element_value($location, 'Type'));

      $item->set('street', $this->casella_feeds_get_xml_element_value($location, 'Street'));
      $item->set('city', $this->casella_feeds_get_xml_element_value($location, 'City'));
      $item->set('state', $this->casella_feeds_get_xml_element_value($location, 'State'));
      $item->set('zip', $this->casella_feeds_get_xml_element_value($location, 'Zip'));
      $item->set('lat', $this->casella_feeds_get_xml_element_value($location, 'Lat'));
      $item->set('lng', $this->casella_feeds_get_xml_element_value($location, 'Lon'));

      $item->set('published', 'On' == $this->casella_feeds_get_xml_element_value($location, 'PubStat'));

      // Need to handle the towns, there can be a bunch.
      // $item->set('towns', $this->casella_feeds_get_towns($location, 'Town'));
      // Need to handle the hours, they need to be split.
      $item->set('hours', $this->parseHours($this->casella_feeds_get_xml_element_value($location, 'Hours')));
      $item->set('serviceReference', $this->casella_feeds_get_xml_element_value($location, 'DivRef'));
      $item->set('phone', $this->casella_feeds_get_xml_element_value($location, 'Cust'));

      // $result->addItem($item);
    }

    return $result;
  }

  /**
   * Pull the hours apart at the seams.
   *
   * @param string $hours
   * @return string
   */
  private function parseHours($hours) {
    if (!$hours || '' == $hours) {
      return '';
    }

    return preg_replace('/([AP]M)([A-Z])/', "$1\r\n$2", $hours);
  }

  private function casella_feeds_get_xml_element_value($xmlElement, $attribute) {
    if (!$xmlElement->$attribute) {
      return '';
    }

    return trim((string) $xmlElement->$attribute);
  }

  private function casella_feeds_get_towns($xmlElement, $attribute) {
    if (!$xmlElement->$attribute) {
      return '';
    }

    kint($xmlElement->$attribute);
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'guid' => [
        'label' => $this->t('GUID'),
        'description' => $this->t("The town's guid, pulled from the importer."),
        'suggestions' => [
          'targets' => ['guid'],
        ],
      ],
      'title' => [
        'label' => $this->t('Title'),
      ],
      'type' => [
        'label' => $this->t('Type'),
        'description' => $this->t("Service location type."),
      ],
      'street' => [
        'label' => $this->t('Street Address'),
      ],
      'city' => [
        'label' => $this->t('City'),
      ],
      'state' => [
        'label' => $this->t('State'),
      ],
      'zip' => [
        'label' => $this->t('Zipcode'),
      ],
      'lat' => [
        'label' => $this->t('Latitude'),
      ],
      'lng' => [
        'label' => $this->t('Longitude'),
      ],
      'published' => [
        'label' => $this->t('Published'),
        'description' => $this->t('Published status'),
      ],
      'towns' => [
        'label' => $this->t('Towns Title Reference'),
      ],
      'hours' => [
        'label' => $this->t('Hours'),
      ],
      'serviceReference' => [
        'label' => $this->t('Service Title Reference'),
      ],
      'phone' => [
        'label' => $this->t('Phone Number'),
      ]
    ];
  }
}
