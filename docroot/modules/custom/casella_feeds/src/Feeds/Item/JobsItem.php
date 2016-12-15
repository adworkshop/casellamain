<?php

namespace Drupal\casella_feeds\Feeds\Item;

use Drupal\feeds\Feeds\Item\BaseItem;

/**
 * Defines an item class for use with the casella towns parser.
 */
class JobsItem extends BaseItem {
  protected $guid;
  protected $title;
  protected $description;
  protected $status;
  protected $category;
  protected $pubDate;
  protected $locationName;
  protected $published;
}
