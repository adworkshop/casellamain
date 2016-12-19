<?php

namespace Drupal\casella_feeds\Feeds\Item;

use Drupal\feeds\Feeds\Item\BaseItem;

/**
 * Defines an item class for use with the casella dropoff parser.
 */
class ServiceLocationItemAdvanced extends BaseItem {
  protected $guid;
  protected $title;
  protected $type;
  protected $street;
  protected $city;
  protected $state;
  protected $zip;
  protected $lat;
  protected $lng;
  protected $published;

  protected $towns;
  protected $hours;
  protected $serviceReference;
  protected $phone;
}
