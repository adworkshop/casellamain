<?php
/**
 * Implements hook_views_query_alter().
 */

function casella_misc_views_query_alter(Drupal\views\ViewExecutable $view, Drupal\views\Plugin\views\query\Sql $query) {
  if ('locations' == $view->id()) {
    if ('block_5' == $view->getDisplay()->display['id']) {
      // Kill the view if there is no search.
      if (!isset($_GET['name'])) {
        $query->addWhereExpression(0, '1 != 1');
        return;
      }

      // Load up the town term.
      // \Drupal\taxonomy\Entity\Term::load();
      $db = \Drupal\Core\Database\Database::getConnection();
      $townCoords = $db->select('taxonomy_term_field_data', 'taxonomy_term_field_data');
      $townCoords->addJoin('INNER', '	taxonomy_term__field_latitude', 'taxonomy_term__field_latitude', 'taxonomy_term__field_latitude.entity_id = taxonomy_term_field_data.tid AND taxonomy_term__field_latitude.bundle = :bundle', array(':bundle' => 'towns_cities'));
      $townCoords->addJoin('INNER', '	taxonomy_term__field_longitude', 'taxonomy_term__field_longitude', 'taxonomy_term__field_longitude.entity_id = taxonomy_term_field_data.tid AND taxonomy_term__field_longitude.bundle = :bundle', array(':bundle' => 'towns_cities'));
      $townCoords->addField('taxonomy_term__field_latitude', 'field_latitude_value');
      $townCoords->addField('taxonomy_term__field_longitude', 'field_longitude_value');
      $townCoords->condition('name', str_replace('"', '', $_GET['name']), '=');

      $townCoords = $townCoords->execute();

      $townCoordResults = $townCoords->fetchAll(\PDO::FETCH_ASSOC);

      if (!$townCoordResults) {
        $query->addWhereExpression(0, '1 != 1');
        return;
      }

      // First, do a quick and dirty 100 mile square cuttoff.
      // Note: 69 is the number of miles in 1 degree of latitude, 49.7 is the
      // approximation for 1 degree of longitude in the adirondack area.
      $query->addWhereExpression(0, 'ABS((node__field_longitude.field_longitude_value - :lonVal) * 69) < 100 ', array(':latVal' => $townCoordResults[0]['field_latitude_value']));
      $query->addWhereExpression(0, 'ABS((node__field_latitude.field_latitude_value - :latVal) * 49.7) < 100 ', array(':lonVal' => $townCoordResults[0]['field_longitude_value']));

      // Now add the expression for the order by.
      $query->addField(NULL, '
      SQRT(
        POW((node__field_longitude.field_longitude_value - :lonVal) * 49.7, 2) +
        POW((node__field_latitude.field_latitude_value - :latVal) * 69, 2)
      )', 'calc_distance', array(':latVal' => $townCoordResults[0]['field_latitude_value'], ':lonVal' => $townCoordResults[0]['field_longitude_value']));

      // Drop the old order.
      $query->orderby = array();
      // Add the new one.
      $query->addOrderBy(NULL, NULL, 'ASC', 'calc_distance');
    }
  }
}
