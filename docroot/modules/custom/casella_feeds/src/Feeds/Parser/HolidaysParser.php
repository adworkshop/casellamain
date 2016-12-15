<?php

namespace Drupal\casella_feeds\Feeds\Parser;

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
 *   id = "casella_holidays",
 *   title = @Translation("Casella Holidays XML"),
 *   description = @Translation("Parse Holidays XML format feeds.")
 * )
 */
class HolidaysParser extends PluginBase implements ParserInterface {
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

    $db = \Drupal\Core\Database\Database::getConnection();
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $paragraphStorage = \Drupal::entityTypeManager()->getStorage('paragraph');

    // Keep track of the guids we have used so we can tank the rest post import.
    $goodGuids = array();

    // @TODO check for holidays to delete.
    foreach ($xml->holiday as $item) {
      $rawTitle = $this->casella_feeds_get_xml_element_value($item, 'Title');
      $title = preg_replace('{\sis\s.+$}', '', $rawTitle);

      // get the location node.
      $node = $this->getNodeFromTitle($title, $db, $nodeStorage);
      if (!$node) {
        continue;
      }

      // Check if the guid matches an existing holiday
      $targetGuid = $this->casella_feeds_get_xml_element_value($item, 'ID');
      $goodGuids[] = $targetGuid;
      $currentHoliday = $this->getMatchingHolidayFromNode($node, $paragraphStorage, $targetGuid);

      if ($currentHoliday) {
        $this->updateHolidayOnNode($currentHoliday, $item);
      }
      else {
        $this->addHolidayToNode($node, $item, $paragraphStorage);
      }
    }

    $this->deleteMissingGuids($db, $goodGuids, $paragraphStorage);

    return $result;
  }

  /**
   * Load a node object from the title.
   *
   * @param string $title
   * @param \Drupal\Core\Database\Connection $db
   * @param \Drupal\Core\Entity\EntityStorageInterface $nodeStorage
   * @return \Drupal\Core\Entity\EntityInterface|bool
   */
  private function getNodeFromTitle($title, $db, $nodeStorage) {
    // Need to get the nid from the title.
    $getTitle = $db->select('node_field_data', 'node_field_data')
      ->fields('node_field_data', array('nid'));
    $getTitle->condition('type', 'location', '=')
      ->condition('title', $title, '=');
    $nidResponse = $getTitle->execute();

    if (!$nidResponse) {
      return FALSE;
    }

    $nidResponse = $nidResponse->fetchAll(\PDO::FETCH_ASSOC);
    if (!count($nidResponse)) {
      return FALSE;
    }

    return $nodeStorage->load($nidResponse[0]['nid']);
  }

  /**
   * @param \Drupal\node\Entity\Node $node
   * @param \Drupal\Core\Entity\EntityStorageInterface $paragraphStorage
   * @return \Drupal\Core\Entity\EntityInterface|bool
   */
  private function getMatchingHolidayFromNode($node, $paragraphStorage, $targetGuid) {
    // Load the current holidays.
    $holidays = $node->get('field_holiday_schedule');
    $holidayIds = array();
    if ($holidays) {
      $holidays = $holidays->getValue();
      foreach($holidays as $holiday) {
        $holidayIds[] = $holiday['target_id'];
      }
    }

    $currentHolidays = $paragraphStorage->loadMultiple($holidayIds);
    foreach ($currentHolidays as $currentHoliday) {
      $guid = $currentHoliday->get('field_guid');
      if ($guid) {
        $guid = $guid->getValue();
        if ($guid[0]['value'] == $targetGuid) {
          return $currentHoliday;
        }
      }
    }

    return FALSE;
  }

  /**
   * Update an existing holiday entity.
   * @param \Drupal\paragraphs\Entity\Paragraph $currentHoliday
   * @param $item
   */
  private function updateHolidayOnNode($currentHoliday, $item) {
    $currentHoliday->set('field_title', $this->casella_feeds_get_xml_element_value($item, 'Holiday'));
    // Need to massage the date field.
    $date = $this->formatDate($this->casella_feeds_get_xml_element_value($item, 'Date'));
    $currentHoliday->set('field_date', $date);
    $currentHoliday->set('field_published', 1);
    $currentHoliday->set('field_service_status', $this->casella_feeds_get_xml_element_value($item, 'Status') != 'Closed' ? '1' : '0');
    $currentHoliday->set('field_service_schedule', $this->casella_feeds_get_xml_element_value($item, 'Notes'));

    $currentHoliday->save();
  }

  /**
   * @param \Drupal\node\Entity\Node $node
   * @param $item
   * @param \Drupal\Core\Entity\EntityStorageInterface $paragraphStorage
   */
  private function addHolidayToNode($node, $item, $paragraphStorage) {
    $date = $this->formatDate($this->casella_feeds_get_xml_element_value($item, 'Date'));
    $newValues = array(
      'type' => 'holiday',
      'field_guid' => $this->casella_feeds_get_xml_element_value($item, 'ID'),
      'field_title' => $this->casella_feeds_get_xml_element_value($item, 'Holiday'),
      'field_date' => $date,
      'field_published' => 1,
      'field_service_status' => $this->casella_feeds_get_xml_element_value($item, 'Status') != 'Closed' ? '1' : '0',
      'field_service_schedule' => $this->casella_feeds_get_xml_element_value($item, 'Notes'),
    );

    $newHoliday = $paragraphStorage->create($newValues);
    $newHoliday->save();

    $node->field_holiday_schedule->appendItem($newHoliday);
    $node->save();
  }

  /**
   * @param \Drupal\Core\Database\Database $db
   * @param array $goodGuids
   * @param \Drupal\Core\Entity\EntityStorageInterface $paragraphStorage
   */
  private function deleteMissingGuids($db, $goodGuids, $paragraphStorage) {
    // Need to snag all the bad holiday guids.
    $badHolidaySelect = $db->select('paragraph__field_guid');
    $badHolidaySelect->fields('paragraph__field_guid', array('entity_id'));
    $badHolidaySelect->condition('bundle', 'holiday', '=');
    $badHolidaySelect->condition('field_guid_value', $goodGuids, 'NOT IN');

    $badHolidays = $badHolidaySelect->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $badHolidays = array_map(function($val){return $val['entity_id'];}, $badHolidays);
    $badHolidays = $paragraphStorage->loadMultiple($badHolidays);
    $paragraphStorage->delete($badHolidays);
  }

  /**
   * @param string $date
   * @return string
   */
  private function formatDate($date) {
    if (!preg_match('/(?:[0-9]+\/){2}[0-9]+/', $date)) {
      return '';
    }

    // pull them apart...
    $dateBits = explode('/', $date);
    // and put them back together...
    $retDate = '';
    $retDate .= strlen($dateBits[2]) == 4 ? $dateBits[2] . '-': '20' . $dateBits[2] . '-';
    $retDate .= strlen($dateBits[0]) == 2 ? $dateBits[0] . '-': '0' . $dateBits[0] . '-';
    $retDate .= strlen($dateBits[1]) == 2 ? $dateBits[1] : '0' . $dateBits[1];
    return $retDate;
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
      'location' => [
        'label' => $this->t('Location'),
      ],
      'holiday' => [
        'label' => $this->t('Holiday'),
      ],
      'date' => [
        'label' => $this->t('Date'),
      ],
      'status' => [
        'label' => $this->t('Status'),
      ],
      'delay_notes' => [
        'label' => $this->t('Delay Notes'),
      ],
    ];
  }
}
