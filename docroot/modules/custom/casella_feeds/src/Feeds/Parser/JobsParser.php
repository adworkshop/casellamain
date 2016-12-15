<?php

namespace Drupal\casella_feeds\Feeds\Parser;

use Drupal\feeds\Component\XmlParserTrait;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\casella_feeds\Feeds\Item\JobsItem;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;

/**
 * Defines a SitemapXML feed parser.
 *
 * @FeedsParser(
 *   id = "casella_jobs",
 *   title = @Translation("Casella Jobs XML"),
 *   description = @Translation("Parse Jobs XML format feeds.")
 * )
 */
class JobsParser extends PluginBase implements ParserInterface {
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
      $jobsItem = new JobsItem();

      $jobsItem->set('guid', $this->casella_feeds_get_xml_element_value($item, 'guid'));
      $jobsItem->set('title', $this->casella_feeds_get_xml_element_value($item, 'title'));
      $jobsItem->set('description', html_entity_decode($this->casella_feeds_get_xml_element_value($item, 'description')));
      $jobsItem->set('status', $this->casella_feeds_get_xml_element_value($item, 'category'));
      $jobsItem->set('category', $this->casella_feeds_get_xml_element_value($item, 'enclosure'));
      $jobsItem->set('pubDate', $this->casella_feeds_get_xml_element_value($item, 'pubDate'));
      $jobsItem->set('locationName', $this->casella_feeds_get_xml_element_value($item, 'comments'));
      $jobsItem->set('published', 'Yes' == $this->casella_feeds_get_xml_element_value($item, 'link'));

      $result->addItem($jobsItem);
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
        'description' => $this->t("The town's city."),
      ],
      'status' => [
        'label' => $this->t('Job Status'),
      ],
      'category' => [
        'label' => $this->t('Job Category'),
      ],
      'pubDate' => [
        'label' => $this->t('Published Date'),
      ],
      'locationName' => [
        'label' => $this->t('Location Name'),
      ],
      'published' => [
        'label' => $this->t('Published'),
      ],
    ];
  }
}
