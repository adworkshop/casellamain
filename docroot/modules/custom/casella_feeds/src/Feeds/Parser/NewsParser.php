<?php

namespace Drupal\casella_feeds\Feeds\Parser;

use Drupal\feeds\Component\XmlParserTrait;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\casella_feeds\Feeds\Item\NewsItem;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;

/**
 * Defines a News XML feed parser.
 *
 * @FeedsParser(
 *   id = "casella_news",
 *   title = @Translation("Casella News XML"),
 *   description = @Translation("Parse News XML format feeds.")
 * )
 */
class NewsParser extends PluginBase implements ParserInterface {
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

    foreach ($xml->channel->item as $item) {
      $newsItem = new NewsItem();

      $newsItem->set('guid', $this->casella_feeds_get_xml_element_value($item, 'guid'));
      $newsItem->set('title', $this->casella_feeds_get_xml_element_value($item, 'title'));
      $newsItem->set('description', html_entity_decode($this->casella_feeds_get_xml_element_value($item, 'description')));
      $newsItem->set('pubDate', $this->casella_feeds_get_xml_element_value($item, 'pubDate'));
      $newsItem->set('published', 'Yes' == $this->casella_feeds_get_xml_element_value($item, 'link'));

      $result->addItem($newsItem);
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
        'suggestions' => [
          'targets' => ['guid'],
        ],
      ],
      'title' => [
        'label' => $this->t('Title'),
      ],
      'description' => [
        'label' => $this->t('Description'),
        'description' => $this->t("The news text."),
      ],
      'pubDate' => [
        'label' => $this->t('Published Date'),
      ],
      'published' => [
        'label' => $this->t('Published'),
      ],
    ];
  }
}
