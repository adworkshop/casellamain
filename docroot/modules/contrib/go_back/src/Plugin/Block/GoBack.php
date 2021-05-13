<?php

namespace Drupal\go_back\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\go_back\Traits\GoBackEntityTypesTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a Go Back Block.
 *
 * @Block(
 *   id = "go_back",
 *   admin_label = @Translation("Go Back Block"),
 *   category = @Translation("Go Back Block"),
 * )
 */
class GoBack extends BlockBase implements ContainerFactoryPluginInterface {
  use GoBackEntityTypesTrait;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;
  /**
   * The language service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageInt;
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Represents the current path for the current request.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $pathStack;
  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;
  /**
   * Trigger cache kill switch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  private $killSwitch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('path.current'),
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * This function construct a block.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The The route match service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageInt
   *   The The route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   * @param \Drupal\Core\Path\CurrentPathStack $pathStack
   *   The current path stack service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   The kill switch service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $routeMatch, LanguageManagerInterface $languageInt, EntityTypeManagerInterface $entityTypeManager, Messenger $messenger, CurrentPathStack $pathStack, KillSwitch $killSwitch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
    $this->languageInt = $languageInt;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->pathStack = $pathStack;
    $this->killSwitch = $killSwitch;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = ['custom_url' => '', 'particular_path' => []];
    // Populate the default configuration with the available entity types and
    // bundles.
    $entityTypes = $this->getContentEntityTypes();
    foreach ($entityTypes as $type => $label) {
      $bundles = $this->getContentEntityTypeBundles($type);
      foreach ($bundles as $bundle => $bundleLabel) {
        $config[$type][$bundle] = [
          'quick_display' => 0,
          'smart_mode' => 0,
          'go_back' => '',
        ];
      }
    }
    return $config + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['active_description'] = [
      '#type' => 'item',
      '#title' => $this->t("How to use Active button."),
      '#description' => $this->t("Allows to show the block in the content type."),
    ];
    $form['smart_description'] = [
      '#type' => 'item',
      '#title' => $this->t("How to use Mode smart."),
      '#description' => $this->t("It allows us to add a custom url to the block button so that the user can go where we want, the customization is independent for each type of content. This url will be used also if the user comes from outside the site and the smart mode is activated."),
    ];
    $form['url_description'] = [
      '#type' => 'item',
      '#title' => $this->t("How to use URL Back (Custom URL)."),
      '#description' => $this->t("The button of the block will take as url the last one of the site that we have visited, we can activate this option for each type of content, in the case that we come from outside the site, the url custom will be url."),
    ];
    $form['path_description'] = [
      '#type' => 'item',
      '#title' => $this->t("Particular path."),
      '#description' => $this->t("Add a specific route where you want the go back block to appear."),
    ];
    $form['custom_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Particular path"),
      '#description' => $this->t('Separate the paths by comma. Example: /home, /contact'),
      '#default_value' => $config['custom_url'],
      '#attributes' => ['data-disable-refocus' => 'true'],
      '#ajax' => [
        'callback' => [$this, 'updateParticularPath'],
        'wrapper' => 'go-back-particular-path',
      ],
      '#element_validate' => [[get_class($this), 'elementValidateDrupalPaths']],
    ];

    // Method ->getCompleteFormState() call should be removed after
    // https://www.drupal.org/project/drupal/issues/2798261 fix.
    $custom_url_value = $form_state->getCompleteFormState()->getValue(
      ['settings', 'custom_url']
    );
    $custom_url_value = $custom_url_value ? $custom_url_value : $config['custom_url'];

    $form['particular_path'] = [
      '#type' => 'details',
      '#open' => (bool) $custom_url_value,
      '#title' => $this->t("Particular path (Config)"),
      '#prefix' => '<div id="go-back-particular-path">',
      '#suffix' => '</div>',
    ];

    if ($custom_url_value) {
      $custom_url_array = explode(', ', $custom_url_value);

      foreach ($custom_url_array as $custom_url) {
        $form['particular_path'][$custom_url] = [
          '#type' => 'fieldset',
          '#title' => $custom_url,
        ];
        $form['particular_path'][$custom_url]['quick_display'] = [
          '#type' => 'checkbox',
          '#title' => $this->t("Active button in %custom_url", [
            '%custom_url' => $custom_url,
          ]),
          '#options' => $custom_url,
          '#default_value' => $config['particular_path'][$custom_url]['quick_display'],
          '#attributes' => [
            'placeholder' => $this->t("Active button in %custom_url", [
              '%custom_url' => $custom_url,
            ]),
          ],
        ];
        $form['particular_path'][$custom_url]['smart_mode'] = [
          '#type' => 'checkbox',
          '#title' => $this->t("Active smart mode in %custom_url", [
            '%custom_url' => $custom_url,
          ]),
          '#default_value' => $config['particular_path'][$custom_url]['smart_mode'],
          '#states' => [
            'visible' => [
              ':input[name$="[particular_path][' . $custom_url . '][quick_display]"]' => [
                'checked' => TRUE,
              ],
            ],
          ],
        ];
        $form['particular_path'][$custom_url]['custom_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t("URL Back - %content_type", [
            '%content_type' => $custom_url,
          ]),
          '#default_value' => $config['particular_path'][$custom_url]['custom_url'],
          '#element_validate' => [
            [get_class($this), 'elementValidateDrupalPaths'],
          ],
          '#states' => [
            'visible' => [
              ':input[name$="[particular_path][' . $custom_url . '][quick_display]"]' => [
                'checked' => TRUE,
              ],
            ],
            'required' => [
              ':input[name$="[particular_path][' . $custom_url . '][quick_display]"]' => [
                'checked' => TRUE,
              ],
            ],
          ],
        ];
      }
    }

    $form['content_entities'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Go Back settings by entity type'),
      '#parents' => ['content_entities'],
    ];
    $contentEntityTypes = $this->getContentEntityTypes();
    foreach ($contentEntityTypes as $type => $label) {
      $form[$type] = [
        '#type' => 'details',
        '#title' => $label,
        '#group' => 'content_entities',
      ];
      $bundles = $this->getContentEntityTypeBundles($type);
      foreach ($bundles as $bundle => $bundleLabel) {
        $form[$type][$bundle] = [
          '#type' => 'fieldset',
          '#title' => $bundleLabel,
        ];
        $form[$type][$bundle]['quick_display'] = [
          '#type' => 'checkbox',
          '#title' => $this->t("Active button in %content_type", [
            '%content_type' => $bundleLabel,
          ]),
          '#default_value' => $config[$type][$bundle]['quick_display'],
          '#attributes' => [
            'placeholder' => $this->t("Active button in %content_type", [
              '%content_type' => $bundle,
            ]),
          ],
        ];
        $form[$type][$bundle]['smart_mode'] = [
          '#type' => 'checkbox',
          '#title' => $this->t("Active smart mode in %content_type", [
            '%content_type' => $bundleLabel,
          ]),
          '#default_value' => $config[$type][$bundle]['smart_mode'],
          '#states' => [
            'visible' => [
              ':input[name$="[' . $bundle . '][quick_display]"]' => [
                'checked' => TRUE,
              ],
            ],
          ],
        ];
        $form[$type][$bundle]['go_back'] = [
          '#type' => 'textfield',
          '#title' => $this->t("URL Back - %content_type", [
            '%content_type' => $bundleLabel,
          ]),
          '#default_value' => $config[$type][$bundle]['go_back'],
          '#element_validate' => [
            [get_class($this), 'elementValidateDrupalPaths'],
          ],
          '#states' => [
            'visible' => [
              ':input[name$="[' . $bundle . '][quick_display]"]' => [
                'checked' => TRUE,
              ],
            ],
            'required' => [
              ':input[name$="[' . $bundle . '][quick_display]"]' => [
                'checked' => TRUE,
              ],
            ],
          ],
        ];
      }
    }
    return $form;
  }

  /**
   * Validation handler to validate internal paths.
   */
  public static function elementValidateDrupalPaths(&$element, FormStateInterface $form_state) {
    // Get the path(s).
    $value = str_replace(' ', '', $element['#value']);
    if (!empty($value)) {
      $paths = explode(',', $value);
      $invalidPaths = [];
      foreach ($paths as $path) {
        // Loop through the path(s) and if the path is not considered valid by
        // Drupal path validator or the path doesn't start with a slash, then we
        // throw an error.
        if (!\Drupal::pathValidator()->isValid($path) || strpos($path, '/', 0) === FALSE) {
          $invalidPaths[] = $path;
        }
      }
      if (!empty($invalidPaths)) {
        // Build the error message formatted to plural.
        $message = \Drupal::translation()->formatPlural(count($invalidPaths), 'The %path path is not valid.', 'The %path paths are not valid.', [
          '%path' => implode(', ', $invalidPaths),
        ]);
        $form_state->setError($element, $message);
      }
    }
  }

  /**
   * Handles Particular path configuration display.
   */
  public function updateParticularPath($form, FormStateInterface $form_state) {
    return $form['settings']['particular_path'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    // Store the entity types configuration, by entity type and bundle.
    $contentEntityTypes = $this->getContentEntityTypes();
    foreach ($contentEntityTypes as $type => $label) {
      if (isset($values[$type])) {
        foreach ($values[$type] as $bundle => $settings) {
          $this->configuration[$type][$bundle] = $settings;
        }
      }
    }

    // Reset the old stored values. Good for removal.
    $this->configuration['particular_path'] = [];
    // Get the custom urls (paths), but only continue if we have at least one.
    $customUrls = str_replace(' ', '', $values['custom_url']);
    $customUrls = !empty($customUrls) ? explode(',', $customUrls) : [];
    foreach ($customUrls as $customUrl) {
      $this->configuration['particular_path'][$customUrl] = $values['particular_path'][$customUrl];
    }
    $this->configuration['custom_url'] = $values['custom_url'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $result = [];
    $config = $this->getConfiguration();

    // Manage particular paths.
    $current_path = $this->pathStack->getPath();
    // The block appears in the url indicated in the configuration.
    if (strpos($config['custom_url'], $current_path) !== FALSE) {
      $result = $this->getQuickDisplayBuild('particular_path', $current_path);
    }

    // Manage entities.
    $currentEntity = $this->getCurrentEntity();
    if (!empty($currentEntity)) {
      $type = function_exists('array_key_first') ? array_key_first($currentEntity) : key($currentEntity);
      $entity = reset($currentEntity);
      if ($entity instanceof ContentEntityInterface) {
        // Some entities do not have a bundle, so we are using the entity type
        // instead.
        $bundle = !empty($entity->bundle()) ? $entity->bundle() : $type;
        $result = $this->getQuickDisplayBuild($type, $bundle);
      }
    }

    // Trigger cache kill switch, see: https://drupal.stackexchange.com/a/151289
    $this->killSwitch->trigger();
    return $result;
  }

  /**
   * Builds the output for the quick display and smart mode settings.
   *
   * @param string $parent
   *   The parent config name. E.g. the entity type machine name for entities.
   * @param string $child
   *   The child config name. E.g. the bundle of the entity type for entities.
   *
   * @return array
   *   Returns a renderable array with the output.
   */
  protected function getQuickDisplayBuild($parent, $child) {
    $result = [];
    $config = $this->getConfiguration();
    if (!empty($config[$parent][$child]['quick_display'])) {
      // We need to prepare the url setting name because the one from entities
      // is different than that one from particular url.
      $setting = isset($config[$parent][$child]['go_back']) ? 'go_back' : 'custom_url';
      if (!empty($config[$parent][$child]['smart_mode']) && !empty($_SERVER['HTTP_REFERER'])) {
        // If smart mode is active and we have a previous page accessed from our
        // site, we set the previous url.
        // @todo: I didn't see where in the original code they where checking if the previous url belongs to current site, so I left it as it is.
        $url = $_SERVER['HTTP_REFERER'];
      }
      elseif (isset($config[$parent][$child][$setting]) && substr($config[$parent][$child][$setting], 0, 1) == '/') {
        // When smart mode is not active, we use the url defined in the
        // settings.
        $url = Url::fromUri('base:' . $config[$parent][$child][$setting])->toString();
      }
      if (!empty($url)) {
        $result = [
          '#theme' => 'block__goback',
          '#link' => $url,
          '#attached' => [
            'library' => [
              'go_back/go_back',
            ],
          ],
        ];
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $currentEntity = $this->getCurrentEntity();
    if (!empty($currentEntity)) {
      $type = function_exists('array_key_first') ? array_key_first($currentEntity) : key($currentEntity);
      $entity = reset($currentEntity);
      if ($entity instanceof EntityInterface) {
        return Cache::mergeTags(parent::getCacheTags(), ["$type:" . $entity->id()]);
      }
    }
    return parent::getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
