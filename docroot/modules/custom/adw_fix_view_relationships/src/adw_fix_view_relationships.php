<?php
/**
 * Implements hook_module_implements_alter().
 */
function casella_module_implements_alter(&$implementations, $hook) {
  $node_access_alter_hooks = array(
    'query_node_access_alter',
    'query_entity_field_access_alter',
  );
  if (in_array($hook, $node_access_alter_hooks)) {
    if (module_exists('view_unpublished')) {
      unset($implementations['node']);
    }
    else {
      unset($implementations[casella]);
    }
  }
}

/**
 * Implements hook_query_TAG_alter().
 *
 * This is the hook_query_alter() for queries tagged with 'node_access'. It adds
 * node access checks for the user account given by the 'account' meta-data (or
 * global $user if not provided), for an operation given by the 'op' meta-data
 * (or 'view' if not provided; other possible values are 'update' and 'delete').
 */
function casella_query_node_access_alter(QueryAlterableInterface $query) {
  node_query_node_access_alter($query, 'node');
}

/**
 * Implements hook_query_TAG_alter().
 *
 * This function implements the same functionality as
 * node_query_node_access_alter() for the SQL field storage engine. Node access
 * conditions are added for field values belonging to nodes only.
 */
function casella_query_entity_field_access_alter(QueryAlterableInterface $query) {
  node_query_node_access_alter($query, 'entity');
}

/**
 * Helper for node access functions.
 * Created from _node_query_node_access_alter()
 *
 * Queries tagged with 'node_access' that are not against the {node} table
 * should add the base table as metadata. For example:
 * @code
 *   $query
 *     ->addTag('node_access')
 *     ->addMetaData('base_table', 'taxonomy_index');
 * @endcode
 * If the query is not against the {node} table, an attempt is made to guess
 * the table, but is not recommended to rely on this as it is deprecated and not
 * allowed in Drupal 8. It is always safer to provide the table.
 *
 * @param $query
 *   The query to add conditions to.
 * @param $type
 *   Either 'node' or 'entity' depending on what sort of query it is. See
 *   node_query_node_access_alter() and node_query_entity_field_access_alter()
 *   for more.
 */
function casella_node_query_node_access_alter($query, $type) {
  global $user;

  // Read meta-data from query, if provided.
  if (!$account = $query->getMetaData('account')) {
    $account = $user;
  }
  if (!$op = $query->getMetaData('op')) {
    $op = 'view';
  }

  // If $account can bypass node access, or there are no node access modules,
  // or the operation is 'view' and the $account has a global view grant
  // (such as a view grant for node ID 0), we don't need to alter the query.
  if (user_access('bypass node access', $account)) {
    return;
  }
  if (!count(module_implements('node_grants'))) {
    return;
  }
  if ($op == 'view' && node_access_view_all_nodes($account)) {
    return;
  }

  $tables = $query->getTables();
  $base_table = $query->getMetaData('base_table');
  // If no base table is specified explicitly, search for one.
  if (!$base_table) {
    $fallback = '';
    foreach ($tables as $alias => $table_info) {
      if (!($table_info instanceof SelectQueryInterface)) {
        $table = $table_info['table'];
        // If the node table is in the query, it wins immediately.
        if ($table == 'node') {
          $base_table = $table;
          break;
        }
        // Check whether the table has a foreign key to node.nid. If it does,
        // do not run this check again as we found a base table and only node
        // can triumph that.
        if (!$base_table) {
          // The schema is cached.
          $schema = drupal_get_schema($table);
          if (isset($schema['fields']['nid'])) {
            if (isset($schema['foreign keys'])) {
              foreach ($schema['foreign keys'] as $relation) {
                if ($relation['table'] === 'node' && $relation['columns'] === array('nid' => 'nid')) {
                  $base_table = $table;
                }
              }
            }
            else {
              // At least it's a nid. A table with a field called nid is very
              // very likely to be a node.nid in a node access query.
              $fallback = $table;
            }
          }
        }
      }
    }
    // If there is nothing else, use the fallback.
    if (!$base_table) {
      if ($fallback) {
        watchdog('security', 'Your node listing query is using @fallback as a base table in a query tagged for node access. This might not be secure and might not even work. Specify foreign keys in your schema to node.nid ', array('@fallback' => $fallback), WATCHDOG_WARNING);
        $base_table = $fallback;
      }
      else {
        throw new Exception(t('Query tagged for node access but there is no nid. Add foreign keys to node.nid in schema to fix.'));
      }
    }
  }

  // Find all instances of the base table being joined -- could appear
  // more than once in the query, and could be aliased. Join each one to
  // the node_access table.

  $grants = node_access_grants($op, $account);
  if ($type == 'entity') {
    // The original query looked something like:
    // @code
    //  SELECT nid FROM sometable s
    //  INNER JOIN node_access na ON na.nid = s.nid
    //  WHERE ($node_access_conditions)
    // @endcode
    //
    // Our query will look like:
    // @code
    //  SELECT entity_type, entity_id
    //  FROM field_data_something s
    //  LEFT JOIN node_access na ON s.entity_id = na.nid
    //  WHERE (entity_type = 'node' AND $node_access_conditions) OR (entity_type <> 'node')
    // @endcode
    //
    // So instead of directly adding to the query object, we need to collect
    // all of the node access conditions in a separate db_and() object and
    // then add it to the query at the end.
    $node_conditions = db_and();
  }
  foreach ($tables as $nalias => $tableinfo) {
    $table = $tableinfo['table'];
    if (!($table instanceof SelectQueryInterface) && $table == $base_table) {
      // Set the subquery.
      $subquery = db_select('node_access', 'na')
        ->fields('na', array('nid'));

      $grant_conditions = db_or();
      // If any grant exists for the specified user, then user has access
      // to the node for the specified operation.
      foreach ($grants as $realm => $gids) {
        foreach ($gids as $gid) {
          $grant_conditions->condition(db_and()
            ->condition('na.gid', $gid)
            ->condition('na.realm', $realm)
          );
        }
      }

      // Attach conditions to the subquery for nodes.
      if (count($grant_conditions->conditions())) {
        $subquery->condition($grant_conditions);
      }
      $subquery->condition('na.grant_' . $op, 1, '>=');
      $field = 'nid';
      // Now handle entities.
      if ($type == 'entity') {
        // Set a common alias for entities.
        $base_alias = $nalias;
        $field = 'entity_id';
      }
      $subquery->where("$nalias.$field = na.nid");

      if ($tableinfo['join type'] == 'LEFT') {
        $conditions_or = db_or();
        $conditions_or->isNull("$nalias.$field");
        $conditions_or->condition('', $subquery, 'EXISTS');

        // For an entity query, attach the subquery to entity conditions.
        if ($type == 'entity') {
          $node_conditions->condition($conditions_or);
        }
        // Otherwise attach it to the node query itself.
        else {
          $query->condition($conditions_or);
        }
      }
      else {
        // For an entity query, attach the subquery to entity conditions.
        if ($type == 'entity') {
          $node_conditions->exists($subquery);
        }
        // Otherwise attach it to the node query itself.
        else {
          $query->exists($subquery);
        }
      }
    }
  }

  if ($type == 'entity' && count($subquery->conditions())) {
    // All the node access conditions are only for field values belonging to
    // nodes.
    $node_conditions->condition("$base_alias.entity_type", 'node');
    $or = db_or();
    $or->condition($node_conditions);
    // If the field value belongs to a non-node entity type then this function
    // does not do anything with it.
    $or->condition("$base_alias.entity_type", 'node', '<>');
    // Add the compiled set of rules to the query.
    $query->condition($or);
  }
}