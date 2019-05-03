<?php

namespace Drupal\casella_custom;

use Drupal\Core\StringTranslation\StringTranslationTrait;

class ConditionalDate {

  use StringTranslationTrait;

  public function getConditionalDate() {
    $time = new \DateTime();

    if ((int)$time->format('G') >= 00 && (int)$time->format('G') < 12 ) {
      return $this->t('Morning time');
    }
    return $this->t('After noon - ' . $time->format('G'));
  }
}
