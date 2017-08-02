<?php

namespace Drupal\casella_feeds\Feeds\Parser;

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
 *   id = "casella_towns",
 *   title = @Translation("Casella Towns XML"),
 *   description = @Translation("Parse Town XML format feeds.")
 * )
 */
class TownsParser extends PluginBase implements ParserInterface {
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

    foreach ($xml->town as $town) {
      $item = new TownsItem();
      $published_status = '0';

      if ('True' == trim($this->casella_feeds_get_xml_element_value($town, 'PubStat'))) {
        $published_status = '1';
      };

      $item->set('guid', $this->casella_feeds_get_xml_element_value($town, 'ID'));
      $item->set('title', $this->casella_feeds_get_xml_element_value($town, 'Title'));
      $item->set('city', $this->casella_feeds_get_xml_element_value($town, 'City'));
      $item->set('state', $this->casella_feeds_get_xml_element_value($town, 'State'));
      $item->set('zip', $this->casella_feeds_get_xml_element_value($town, 'Zip'));
      $item->set('lat', $this->casella_feeds_get_xml_element_value($town, 'Lat'));
      $item->set('lng', $this->casella_feeds_get_xml_element_value($town, 'Lon'));
      $item->set('servicelocation', $this->casella_feeds_get_xml_element_value($town, 'Div'));
      $item->set('published', $published_status);

      $result->addItem($item);
    }

    \Drupal::logger('my_module')->notice('town ' . $item->get('title') . ' pub status is ' . $item->get('published');

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
        'description' => $this->t("The town's name and state."),
      ],
      'city' => [
        'label' => $this->t('City'),
        'description' => $this->t("The town's city."),
      ],
      'state' => [
        'label' => $this->t('State'),
        'description' => $this->t("The town's state."),
      ],
      'zip' => [
        'label' => $this->t('Zipcode'),
        'description' => $this->t("The town's zipcode"),
      ],
      'lat' => [
        'label' => $this->t('Latitude'),
      ],
      'lng' => [
        'label' => $this->t('Longitude'),
      ],
      'servicelocation' => [
        'label' => $this->t('Service Location'),
        'description' => $this->t("The town's associated service location. Denoted by the tag Div."),
      ],
      'published' => [
        'label' => $this->t('Published'),
        'description' => $this->t('Published status'),
      ],
    ];
  }

}
