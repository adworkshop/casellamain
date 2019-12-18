<?php

namespace Drupal\casella_news\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\aggregator\Entity;
use Drupal\node;
use Drupal\twig_tweak;

class CasellaNewsController extends ControllerBase {

  public function list() {
    // Get the 10 most recent aggregated item ids
    $aggregated_item_ids = [];

    $aggregated_item_ids = \Drupal::entityQuery('aggregator_item')
      ->sort('timestamp', 'desc')
      ->range(0,10)
      ->execute();

    // Prepare data for the first item on the list
    if (count($aggregated_item_ids) > 0) {
      $aggregated_item_id = array_shift($aggregated_item_ids);
      $aggregated_item = Entity\Item::load($aggregated_item_id);
      $aggregated_item_timestamp = 0 + $aggregated_item->timestamp->value;
    }
    else {
      $aggregated_item_timestamp = 0;
    }

    // Get the 10 most recent news ids
    $nids = [];

    $nids = \Drupal::entityQuery('node')
      ->condition('type','news')
      ->condition('status','1')
      ->sort('field_publication_date', 'desc')
      ->range(0,10)
      ->execute();

    // Prepare data for the first node on the list
    if (count($nids) > 0) {
      $nid = array_shift($nids);
      $node = node\Entity\Node::load($nid);
      $node_timestamp = strtotime($node->field_publication_date->value);
    }
    else {
      $node_timestamp = 0;
    }

    $news_items = [];
    for ($i=0; $i < 10; $i++) {
      if ($aggregated_item_timestamp > $node_timestamp) {
        if (count($aggregated_item_ids) > 0) {
          $news_items["$i" . $aggregated_item_timestamp] = $aggregated_item;
          $aggregated_item_id = array_shift($aggregated_item_ids);
          $aggregated_item = Entity\Item::load($aggregated_item_id);
          $aggregated_item_timestamp = 0 + $aggregated_item->timestamp->value;
        }
        elseif ($aggregated_item_timestamp !== 0) {
          $news_items["$i" . $aggregated_item_timestamp] = $aggregated_item;
          $aggregated_item_timestamp = 0;
        }
        else {
          // Out of items on both sides
          break;
        }
      }
      else {
        if (count($nids) > 0) {
          $news_items["$i" . $node_timestamp] = $node;
          $nid = array_shift($nids);
          $node = node\Entity\Node::load($nid);
          $node_timestamp = strtotime($node->field_publication_date->value);
        }
        elseif ($node_timestamp !== 0) {
          $news_items["$i" . $node_timestamp] = $node;
          $node_timestamp = 0;
        }
        else {
          // Out of items on both sides
          break;
        }
      }
    }
    $markup = "";
    foreach ($news_items as $news_item) {
      if (get_class($news_item) === 'Drupal\aggregator\Entity\Item') {
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('aggregator_item');
        $pre_render = $view_builder->view($news_item, "teaser");
        $markup .= render($pre_render);
      }
      else {
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
        $pre_render = $view_builder->view($news_item, "teaser");
        $markup .= render($pre_render);
      }
    }
    return [
      '#markup' => $markup
    ];
  }
}
