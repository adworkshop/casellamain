<?php

namespace Drupal\ngrok_drupal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NgrokDrupalSettingsForm
 *
 * @package Drupal\ngrok_drupal\Form
 */
class NgrokDrupalSettingsForm extends ConfigFormBase {

  public const CONFIG_SETTINGS = 'ngrok_drupal.settings';
  public const DEFAULT_DOMAIN = 'ngrok.io';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [static::CONFIG_SETTINGS];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ngrok_drupal_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_SETTINGS);

    $form['domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ngrok Domain'),
      '#required' => TRUE,
      '#description' => $this->t('The domain configured in Ngrok Dashboard to tunnel against.'),
      '#default_value' => $config->get('domain') ?? static::DEFAULT_DOMAIN,
    ];

    return parent::buildForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::CONFIG_SETTINGS)
      ->set('domain', $form_state->getValue('domain'))
      ->save();

    parent::submitForm($form, $form_state); // TODO: Change the autogenerated stub
  }
}
