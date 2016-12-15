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
      $this->extractBreadcrumbsDefault($breadcrumbs, $position, $menuLink, $menuLinkManager, \Drupal::menuTree());
    }
    else {
      // Not in the nav, check if this is one of the special case nodes.
      $node = \Drupal::routeMatch()->getParameter('node');
      if ($node) {
        $type = $node->getType();

        $baseNid = FALSE;
        if ('job' == $type) {
          $baseNid = '696';
        }
        elseif ('information_post' == $type) {
          $baseNid = '36';
        }
        elseif ('product' == $type) {
          // See if we can get a product category.
          $cat = $node->get('field_product_category');
          if ($cat) {
            $cat = $cat->getValue();
            if ($cat) {
              $baseNid = $cat[0]['target_id'];
            }
          }
          if (!$baseNid) {
            $baseNid = '26';
          }
        }

        if ($baseNid) {
          $trailIds = $menuLinkManager->loadLinksByRoute('entity.node.canonical', array('node' => $baseNid));
          // Pull the IDs.
          $trailIds = array_keys($trailIds);
          // Get the full list. Conveniently comes witht he current ID included.
          $trailIds = $menuLinkManager->getParentIds($trailIds[0]);
          $this->extractBreadcrumbsFromTrail($breadcrumbs, $position, $trailIds, $menuLinkManager);
        }
      }
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
  private function extractBreadcrumbsDefault(&$breadcrumbs, &$position, $menuLink, $menuLinkManager, $menuTree) {
    $menuLink = array_shift($menuLink);
    $currentPluginId = $menuLink->getPluginId();

    // Build the typical default set of menu tree parameters.
    $menu_name = $menuLink->getMenuName();
    $parameters = $menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    $trailIds = $parameters->activeTrail;

    $this->extractBreadcrumbsFromTrail($breadcrumbs, $position, $trailIds, $menuLinkManager, $currentPluginId);
  }

  /**
   * @param $breadcrumbs
   * @param $position
   * @param $trailIds
   * @param $menuLinkManager
   * @param string $currentPluginId
   */
  private function extractBreadcrumbsFromTrail(&$breadcrumbs, &$position, $trailIds, $menuLinkManager, $currentPluginId = '') {
    $db = \Drupal\Core\Database\Database::getConnection();

    foreach (array_reverse($trailIds) as $value) {
      if ($value && $value !== $currentPluginId) {
        $instance = $menuLinkManager->createInstance($value);
        $urlObject = $instance->getUrlObject();
        $title =  $instance->getTitle();

        // If this is a subsection homepage we need to reset the breadcrumbs.
        $nodeType = $this->getType($urlObject, $db);
        if ('subsection_homepage' == $nodeType) {
          $breadcrumbs = new Breadcrumb();
        }

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

  /**
   * Gets the type from a urlObject if it exists.
   * @param $urlObject
   * @param $db
   * @return string
   */
  private function getType($urlObject, $db) {
    $routeParameters = $urlObject->getRouteParameters();
    if (!isset($routeParameters['node'])) {
      return '';
    }

    $nodeType = $serviceResults = $db->select('node', 'node');
    $nodeType->fields('node', array('type'));
    $nodeType->condition('nid', $routeParameters['node'], '=');
    $nodeType = $nodeType->execute();

    if (!$nodeType) {
      return '';
    }

    $nodeTypeAssoc = $nodeType->fetchAll(\PDO::FETCH_ASSOC);
    if (!isset($nodeTypeAssoc[0]['type'])) {
      return '';
    }

    return $nodeTypeAssoc[0]['type'];
  }
}
