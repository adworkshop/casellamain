<?php

namespace Drupal\casella_feeds\Feeds\Parser;

use Drupal\casella_feeds\Feeds\Item\ServiceLocationItem;
use Drupal\feeds\Component\XmlParserTrait;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\casella_feeds\Feeds\Item\TownsItem;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;

/**
 * Defines a SitemapXML feed parser.
 *
 * @FeedsParser(
 *   id = "casella_service_locations",
 *   title = @Translation("Casella Service Locations XML"),
 *   description = @Translation("Parse Service Locations XML format feeds.")
 * )
 */
class ServiceLocationsParser extends PluginBase implements ParserInterface {
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
      $item = new ServiceLocationItem();

      $item->set('guid', $this->casella_feeds_get_xml_element_value($location, 'ID'));
      $item->set('title', $this->casella_feeds_get_xml_element_value($location, 'Title'));
      $type = $this->casella_feeds_get_xml_element_value($location, 'Type');
      if ('Office' == $type) {
        $type = 'Corporate';
      }
      elseif ('Drop-Off' == $type) {
        $type = 'Transfer';
      }
      $item->set('type', $type);
      $item->set('street', $this->casella_feeds_get_xml_element_value($location, 'Street'));
      $item->set('city', $this->casella_feeds_get_xml_element_value($location, 'City'));
      $item->set('state', $this->casella_feeds_get_xml_element_value($location, 'State'));
      $item->set('zip', $this->casella_feeds_get_xml_element_value($location, 'Zip'));
      $item->set('lat', $this->casella_feeds_get_xml_element_value($location, 'Lat'));
      $item->set('lng', $this->casella_feeds_get_xml_element_value($location, 'Lon'));
      $item->set('content', $this->casella_feeds_get_xml_element_value($location, 'Page'));
      $item->set('published', 'On' == $this->casella_feeds_get_xml_element_value($location, 'PubStat'));

      $result->addItem($item);
    }

    return $result;
  }

  private function casella_feeds_get_xml_element_value($xmlElement, $attribute) {
    if (!$xmlElement->$attribute) {
      return '';
    }

    return trim((string) $xmlElement->$attribute);
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
      'content' => [
        'label' => $this->t('Body Content'),
        'description' => $this->t('Body Content. Currently complied from all the page tags'),
      ],
      'published' => [
        'label' => $this->t('Published'),
        'description' => $this->t('Published status'),
      ],
    ];
  }
}
