<?php

namespace Drupal\pc;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds and process a form for editing a PHP Console settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pc_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (!class_exists('PhpConsole\Connector')) {
      drupal_set_message(t('PHP Console library is not installed.'), 'warning');
    }

    $settings = $this->config('pc.settings');

    $ip = \Drupal::request()->getClientIp();

    if (!$form_state->getUserInput()) {
      if (!$this->checkIp($ip)) {
        drupal_set_message(t('Your current IP address %ip is not allowed to access to PHP Console.', ['%ip' => $ip]), 'warning', FALSE);
      }
      else {
        /** @var \PhpConsole\Connector $connector */
        $connector = \Drupal::service('pc.connector_factory')->get();
        if ($connector && !$connector->isActiveClient()) {
          $url = Url::fromUri(
            'https://chrome.google.com/webstore/detail/php-console/nfhmhhlpfleoednkpnnnkolmclajemef',
            ['attributes' => ['target' => 'blank']]
          );
          $extension_link = $this->l(t('PHP Console extension'), $url);
          drupal_set_message(t('You need @extension_link to be installed on Google Chrome.', ['@extension_link' => $extension_link]), 'warning', FALSE);
        }
      }
    }

    $form['password_enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable password protection'),
      '#description' => t('Remote PHP code execution allowed only in password protected mode.'),
      '#default_value' => $settings->get('password_enabled'),
    ];

    // Password field type doesn't support form #states. So we make a wrapper
    // around the field.
    // See https://drupal.org/node/1427838.
    $form['pass_wrapper'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ':input[name="password_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['pass_wrapper']['password'] = [
      '#title' => 'Password',
      '#type' => 'password',
      '#description' => t('Provide a password for client authorization.'),
    ];

    $form['remote_php_execution'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable remote PHP code execution'),
      '#default_value' => $settings->get('remote_php_execution'),
      '#description' => t('Note that it is a dangerous security risk in the hands of a malicious or inexperienced user.'),
      '#states' => [
        'visible' => [
          ':input[name="password_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['ips'] = [
      '#type' => 'textarea',
      '#title' => 'Allowed IP masks',
      '#description' => t('Enter one value per line. Leave empty to disable IP verification.'),
      '#default_value' => $settings->get('ips'),
    ];
    $form['ips']['#description'] .= ' ' . t('Your IP address is: %ip.', ['%ip' => $ip]);

    $form['track_errors'] = [
      '#type' => 'checkbox',
      '#title' => t('Handle PHP errors'),
      '#default_value' => $settings->get('track_errors'),
      '#description' => t('This option does not cancel default Drupal error handler.'),
    ];

    $form['dumper_maximum_depth'] = [
      '#type' => 'number',
      '#title' => t('Maximum dumper depth'),
      '#default_value' => $settings->get('dumper_maximum_depth'),
      '#description' => t('Maximum depth that the dumper should go into the variable.'),
      '#min' => 1,
      '#max' => 100,
    ];

    $options = [
      'server' => t('Server'),
      'session' => t('Session'),
      'cookie' => t('Cookie'),
      'post' => t('Post'),
      'get' => t('Get'),
      'logged_user' => t('Logged user'),
      'route' => t('Current route'),
      'forms' => t('Forms'),
      'memory_usage' => t('Memory usage'),
      'peak_memory_usage' => t('Peak memory usage'),
      'execution_time' => t('Page execution time'),
      'db_queries' => t('DB queries'),
      'watchdog' => t('Watchdog messages'),
      'emails' => t('Outgoing emails'),
    ];
    $form['debug_info'] = [
      '#type' => 'checkboxes',
      '#title' => t('Debug information'),
      '#options' => $options,
      '#default_value' => array_keys(array_filter($settings->get('debug_info'))),
      '#description' => t('These data will be sent to browser console on each page request.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->configFactory()->getEditable('pc.settings');
    $values = $form_state->getValues();
    if ($values['password']) {
      $settings->set('password', $values['password']);
    }
    $settings
      ->set('password_enabled', $values['password_enabled'])
      ->set('remote_php_execution', $values['remote_php_execution'])
      ->set('ips', $values['ips'])
      ->set('track_errors', $values['track_errors'])
      ->set('dumper_maximum_depth', $values['dumper_maximum_depth'])
      ->set('debug_info', $values['debug_info'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Check whether the IP is allowed to connect to PHP Console.
   */
  protected function checkIp($ip) {
    $ips = explode("\n", $this->config('pc.settings')->get('ips'));
    $ips = array_map('trim', $ips);
    $ips = array_filter($ips, 'strlen');

    // Empty $ips means any IPs are allowed.
    if (!$ips) {
      return TRUE;
    }

    foreach ($ips as $allowed_ip_mask) {
      if (preg_match('~^' . str_replace(array('.', '*'), array('\.', '\w+'), $allowed_ip_mask) . '$~i', $ip)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    // @TODO: What should be returned here?
    return [];
  }

}
