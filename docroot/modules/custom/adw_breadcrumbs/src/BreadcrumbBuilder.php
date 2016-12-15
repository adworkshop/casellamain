<?php

/**
 * @file
 * Contains \Drupal\adw_breadcrumbs\BreadcrumbBuilder.
 */

namespace Drupal\adw_breadcrumbs;

use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Component\Utility\Unicode;
use Drupal\system\PathBasedBreadcrumbBuilder;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Adds the current page title to the breadcrumb.
 *
 * Extend PathBased Breadcrumbs to include the current page title as an unlinked
 * crumb. The module uses the path if the title is unavailable and it excludes
 * all admin paths.
 *
 * {@inheritdoc}
 */
class BreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $request = \Drupal::request();
    $route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT);

    // Do not adjust the breadcrumbs on admin paths.
    if ($route && $route->getOption('_admin_route')) {
      return parent::build($route_match);
    }

    $menuLinkManager = \Drupal::service('plugin.manager.menu.link');
    $menuLink = $this->getMenuLink($menuLinkManager, \Drupal::routeMatch(), \Drupal::service('current_route_match'));

    $breadcrumbs = new Breadcrumb();
    $position = 1;
    if (count($menuLink)) {
      $this->extractBreadcrumbs($breadcrumbs, $position, $menuLink, $menuLinkManager, \Drupal::menuTree());
    }

    // Add the current page title.
    $title = $this->titleResolver->getTitle($request, $route);
    if (!isset($title)) {
      // Fallback to using the raw path component as the title if the
      // route is missing a _title or _title_callback attribute.
      $path = trim($this->context->getPathInfo(), '/');
      $pathElements = explode('/', $path);
      $title = str_replace(array('-', '_'), ' ', Unicode::ucfirst(end($pathElements)));
    }
    $breadcrumbs->addLink(Link::createFromRoute($title, '<none>'));

    // Add the full URL path as a cache context, since we will display the
    // current page as part of the breadcrumb.
    $breadcrumbs->addCacheContexts(['url.path']);

    return $breadcrumbs;
  }

  /**
   * Snags the menu link differently if this is a node or something else.
   *
   * @param $menuLinkManager
   * @param $routeMatch
   * @param $routeMatchService
   * @return mixed
   */
  private function getMenuLink($menuLinkManager, $routeMatch, $routeMatchService) {
    // Check to see if this item is in the navigation.
    $node_id = $routeMatch->getRawParameter('node');
    if ($node_id) {
      return $menuLinkManager->loadLinksByRoute('entity.node.canonical', array('node' => $node_id));
    }
    else {
      $routeName = $routeMatchService->getRouteName();
      return $menuLinkManager->loadLinksByRoute($routeName);
    }
  }

  /**
   * Gets the current route and pulls out breadcrumbs for all parents.
   *
   * @param $breadcrumbs
   * @param $position
   * @param $menuLink
   * @param $menuLinkManager
   * @param $menuTree
   */
  private function extractBreadcrumbs(&$breadcrumbs, &$position, $menuLink, $menuLinkManager, $menuTree) {
    $menuLink = array_shift($menuLink);
    $currentPluginId = $menuLink->getPluginId();

    // Build the typical default set of menu tree parameters.
    $menu_name = 'main';
    $parameters = $menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $trailIds = $parameters->activeTrail;

    foreach (array_reverse($trailIds) as $key => $value) {
      if ($value && $value !== $currentPluginId) {
        $urlObject = $menuLinkManager->createInstance($value)->getUrlObject();
        $title =  $menuLinkManager->createInstance($value)->getTitle();

        $schemaElement = (object) array(
          "@type" => "ListItem",
          "position" => $position,
          "item" => (object) array(
            "@id" => $urlObject->toString(),
            "name" => $title,
          ),
        );
        $position++;

        $link = new Link($title, $urlObject);
        $link->{'schema'} = $schemaElement;

        $breadcrumbs->addLink($link);
      }
    }
  }
}
