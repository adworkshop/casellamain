<?php

namespace Drupal\casella_feeds\Feeds\Item;

use Drupal\feeds\Feeds\Item\BaseItem;

/**
 * Defines an item class for use with the casella towns parser.
 */
class NewsItem extends BaseItem {
  protected $guid;
  protected $title;
  protected $description;
  protected $pubDate;
  protected $published;
}
