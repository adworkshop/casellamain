<?php
namespace Drupal\adw_strip_comments\TwigExtension;


class StripComments extends \Twig_Extension {

  /**
  * Generates a list of all Twig filters that this extension defines.
  */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('adw_strip_comments', array($this, 'stripComments')),
    ];
  }

  /**
  * Gets a unique identifier for this Twig extension.
  */
  public function getName() {
    return 'adw_strip_comments.twig_extension';
  }

  /**
  * Replaces all numbers from the string.
  */
  public static function stripComments($string) {
    return preg_replace('/<!--(.|\s)*?-->/', '', $string);
  }
}
