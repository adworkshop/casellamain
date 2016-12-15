<?php

namespace Drupal\casella_feeds\Feeds\Processor;

use Drupal\feeds\Feeds\Processor\EntityProcessorBase;

/**
 * Defines a node processor.
 *
 * Creates nodes from feed items.
 *
 * @FeedsProcessor(
 *   id = "entity:paragraph_on_node",
 *   title = @Translation("Paragraph on Node"),
 *   description = @Translation("Creates paragraphs from feed items and associates them with nodes."),
 *   paragraph_type = "paragraph",
 *   entity_type = "node",
 *   arguments = {"@entity.manager", "@entity.query"},
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Processor\Form\DefaultEntityProcessorForm",
 *     "option" = "Drupal\casella_feeds\Feeds\Processor\Form\ParagraphOnNodeProcessorOptionForm",
 *   },
 * )
 */
class ParagraphOnNodeProcessor extends EntityProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function entityLabel() {
    return $this->t('Paragraph on Node');
  }

  /**
   * {@inheritdoc}
   */
  public function entityLabelPlural() {
    return $this->t('Paragraphs on Nodes');
  }

}
