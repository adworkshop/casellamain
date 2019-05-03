<?php

namespace Drupal\casella_custom\Controller;

use Drupal\Core\Controller\ControllerBase;

class CasellaCustomController extends ControllerBase {

  public function main() {
    $service = \Drupal::service('casella_custom.conditional_date');
    dpm($service);
    return [
      '#markup' => $this->t('Some markup')
    ];

  }
}
