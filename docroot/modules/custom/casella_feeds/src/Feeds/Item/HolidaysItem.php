<?php

namespace Drupal\casella_feeds\Feeds\Item;

use Drupal\feeds\Feeds\Item\BaseItem;

/**
 * Defines an item class for use with the casella holiday parser.
 */
class HolidaysItem extends BaseItem {
  protected $guid;
  protected $location;
  protected $holiday;
  protected $date;
  protected $status;
  protected $delay_notes;
}
