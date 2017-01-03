<?php
namespace Drupal\adw_strip_comments\TwigExtension;


class MachineName extends \Twig_Extension {

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('adw_machine_name', array($this, 'generateMachineName')),
    ];
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'adw_machine_name.twig_extension';
  }

  /**
   * Generates a machine name that can be used in html.
   */
  public static function generateMachineName($string) {
    // Cannot begin with a number or a hyphen.
    // Can only contain letters, numbers, hyphens, and underscores.
    return trim(preg_replace(['/[^0-9A-Za-z\s_-]/', '/^[-0-9\s]+/', '/\s+/'], ['', '', '_'], $string));
  }
}
