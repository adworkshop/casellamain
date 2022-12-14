<?php

/**
 * @file
 * Cacheflush Drush implementation.
 */

use Drupal\cacheflush\Controller\CacheflushApi;

/**
 * Implements hook_drush_help().
 */
function cacheflush_drush_drush_help($command) {
  switch ($command) {
    case 'drush:cacheflush':
      return dt('Clear cache with help of cacheflush module.');
  }
}

/**
 * Implements hook_drush_command().
 */
function cacheflush_drush_drush_command() {
  $items = [];
  $items['cacheflush'] = [
    'description' => dt('Clear cache predefined in cacheflush preset.'),
    'arguments' => [
      'id' => dt('Preset id to run'),
    ],
    'examples' => [
      'Example' => 'drush cacheflush 5',
    ],
    'aliases' => ['cf'],
  ];
  return $items;
}

/**
 * Callback function for drush cacheflush.
 *
 * Callback is called by using drush_hook_command() where
 * hook is the name of the module (cacheflush_drush) and command is the name of
 * the Drush command with all "-" characters converted to "_" characters.
 *
 * @param string|int $id
 *   An optional argument.
 */
function drush_cacheflush_drush_cacheflush($id = NULL) {
  if (isset($id)) {
    if (is_numeric($id)) {
      $cacheflush = cacheflush_load($id);
      if ($cacheflush && $cacheflush->getStatus() == 1) {
        $msg = CacheflushApi::create(\Drupal::getContainer())->clearPresetCache($cacheflush);
        fwrite(STDOUT, $msg . '\n');
      }
      else {
        $msg = t('No entity with this id: "@variable", or entity not published yet.', ['@variable' => $id]);
      }
    }
    else {
      $msg = t('Please provide the ID of the preset (numeric value) ex: "drush cf 1".');
    }
    if (isset($msg)) {
      fwrite(STDOUT, $msg . '\n');;
    }
  }
  else {
    \Drupal::logger('cacheflush_drush')->info(t('Preset list. Use "drush cf ID" to clear cache.'));
    foreach (cacheflush_load_multiple_by_properties(['status' => 1]) as $id => $entity) {
      $msg = '[' . $id . ']   :   ' . $entity->getTitle();
      fwrite(STDOUT, $msg . '\n');
    }
  }

}
