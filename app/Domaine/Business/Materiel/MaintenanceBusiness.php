<?php

namespace App\Domaine\Business\Materiel;

/**
 * Model for manipulating 'maintenance' database table
 * Available public methods
 */
class MaintenanceBusiness
{
  /**
   * Get list of maintenances to display, with next computation, sorted by next
   * @param integer $productId ID of the product for which to find next maintenances
   * @return Collection of #maintenance_existing_basic or #maintenance_control_lastexec
   */
  public static function listMaintenancesNext($productId = null)
  {

    // Prepare subquery if selection is for a product
    $additionals = $productId === null
      ? ""
      : <<<EOF
        ,
        K.exec_id AS exec_id,
        K.exec_last AS exec_last
EOF;
    $subquery = $productId === null
      ? ""
      : <<<EOF
        LEFT JOIN (
          SELECT
            K.id AS maintenance_id,
            E.id AS exec_id,
            K.last AS exec_last
          FROM (
            SELECT
              E.maintenance_id AS id,
              MAX(E.date) AS last
            FROM maintenance_exec E
            GROUP BY E.maintenance_id
          ) K
          LEFT JOIN maintenance_exec E ON K.id = E.maintenance_id AND K.last = E.date
        ) K ON M.id = K.maintenance_id
        WHERE P.id = {$productId}
EOF;

    // Prepare query
    $query = <<<EOF
      SELECT
      	M.id AS id,
      	P.id AS product_id,
      	P.name AS product_name,
      	M.name AS name,
      	M.periodicity AS periodicity,
      	M.outside AS outside,
        MIN(L.execution_date) AS last
        {$additionals}
      FROM maintenance M
      INNER JOIN product P ON M.product_id = P.id
      LEFT JOIN (
      	SELECT
      		I.id AS item_id,
      		M.id AS maintenance_id,
      		MAX(E.date) AS execution_date
      	FROM item I
      	INNER JOIN maintenance_exec_row R ON I.id = R.item_id
      	INNER JOIN maintenance_exec E ON R.exec_id = E.id
      	INNER JOIN maintenance M ON E.maintenance_id = M.id
      	WHERE R.success = 1
          AND I.deleted IS NULL
      	GROUP BY I.id, M.id
      	UNION
      	SELECT
      		I.id AS item_id,
      		M.id AS maintenance_id,
      		DATE('2000-01-01') AS execution_date
      	FROM item I
      	INNER JOIN product P ON I.product_id = P.id
      	INNER JOIN maintenance M ON P.id = M.product_id
      	WHERE (I.id, M.id) NOT IN (
      		SELECT
      			I.id AS item_id,
      			M.id AS maintenance_id
      		FROM item I
      		LEFT JOIN maintenance_exec_row R ON I.id = R.item_id
      		LEFT JOIN maintenance_exec E ON R.exec_id = E.id
      		LEFT JOIN maintenance M ON E.maintenance_id = M.id
      		WHERE R.success = 1
            AND I.deleted IS NULL
      		GROUP BY I.id, M.id
      	)
          AND I.deleted IS NULL
      ) L ON M.id = L.maintenance_id
      {$subquery}
      GROUP BY M.id, L.maintenance_id
      ORDER BY last
EOF;

    // Execute query
    $maintenances = self::db()->select($query);

    $asap = $productId === null
      ? null
      : 'ASAP';

    // Map output to correct JSON format
    return Arrays::each($maintenances, function ($maintenance) use (&$productId, $asap) {

      // Compute next maintenance
      $next = ($maintenance->last === null || $maintenance->last === '2000-01-01')
        ? $asap
        : new \DateTime($maintenance->last);
      if ($next !== $asap) {
        $next->add(new \DateInterval('P' . $maintenance->periodicity . 'M'));
        $next = $next->format('Y-m-d');
      }

      // Return correct JSON object
      return Arrays::merge(
        [
          'next' => $next
        ],
        $productId === null
        ? [
          'id' => $maintenance->id,
          'product' => [
            'id' => $maintenance->product_id,
            'name' => $maintenance->product_name
          ],
          'name' => $maintenance->name,
          'periodicity' => $maintenance->periodicity,
          'outside' => $maintenance->outside === 1
        ]
        : [
          'maintenance' => [
            'id' => $maintenance->id,
            'name' => $maintenance->name,
            'periodicity' => $maintenance->periodicity,
            'outside' => $maintenance->outside === 1
          ]
        ]
      );
    });
  }

  /**
   * List maintenances events for a given item
   * @param integer $itemId ID of the item for which to retrieve history
   * @return Collection of events { date, name, status, message, type, id }
   */
  public static function listMaintenanceEvents($itemId)
  {

    // Build query
    $query = <<<EOF
      SELECT
        R.executed AS executed,
        R.success AS success,
        R.remark AS remark,
        E.id AS id,
        E.date AS date,
        M.id AS maintenance_id,
        M.name AS name
      FROM maintenance_exec_row R
      INNER JOIN maintenance_exec E ON R.exec_id = E.id
      INNER JOIN maintenance M ON E.maintenance_id = M.id
      WHERE R.item_id = ?
EOF;

    // Execute query
    $maintenances = self::db()->select($query, [$itemId]);

    // Map output to correct JSON format
    return array_values(Arrays::each(
      $maintenances,
      function ($maintenance) {
        $status = $maintenance->executed === 0
          ? ItemModel::EVENT_STATUS_WARNING
          : (
            $maintenance->success === 1
            ? ItemModel::EVENT_STATUS_SUCCESS
            : ItemModel::EVENT_STATUS_FAILURE
          );
        return [
          'date' => $maintenance->date,
          'name' => $maintenance->name,
          'status' => $status,
          'message' => $status === ItemModel::EVENT_STATUS_SUCCESS
            ? "Succès"
            : (
              $status === ItemModel::EVENT_STATUS_WARNING
              ? "Pas exécutée"
              : "Echec" . ($maintenance->remark ? " : {$maintenance->remark}" : "")
            ),
          'type' => ItemModel::EVENT_TYPE_MAINTENANCE,
          'id' => $maintenance->maintenance_id . ':' . $maintenance->id
        ];
      }
    ));
  }

  /**
   * List maintenance status for a given item
   * @param integer $itemId ID of the item for which to retrieve history
   * @return Collection of { id, name, last, next } maintenances
   */
  public static function listMaintenanceByItem($itemId)
  {

    // Build query
    $query = <<<EOF
    SELECT
      M.id AS id,
      M.name AS name,
      M.periodicity AS periodicity,
      T.last AS last
    FROM item I
    INNER JOIN product P ON I.product_id = P.id
    INNER JOIN maintenance M ON P.id = M.product_id
    LEFT JOIN (
      SELECT
        M.id AS id,
        MAX(E.date) AS last
      FROM item I
      INNER JOIN product P ON I.product_id = P.id
      INNER JOIN maintenance M ON P.id = M.product_id
      INNER JOIN maintenance_exec E ON M.id = E.maintenance_id
      INNER JOIN maintenance_exec_row R ON E.id = R.exec_id AND I.id = R.item_id AND R.success = 1
      WHERE I.id = ?
      GROUP BY M.id
    ) T ON M.id = T.id
    WHERE I.id = ?
EOF;

    // Execute query
    $maintenances = self::db()->select($query, [$itemId, $itemId]);

    // Map output to correct JSON format
    return array_values(Arrays::each(
      $maintenances,
      function ($maintenance) {

        // Compute next maintenance
        $next = ($maintenance->last === null)
          ? null
          : new \DateTime($maintenance->last);
        if ($next !== null) {
          $next->add(new \DateInterval('P' . $maintenance->periodicity . 'M'));
          $next = $next->format('Y-m-d');
        }

        // Format output
        return [
          'id' => $maintenance->id,
          'name' => $maintenance->name,
          'last' => $maintenance->last,
          'next' => $next
        ];
      }
    ));
  }

  /**
   * Get basic informations for editing purposes
   * @param integer $id ID of the maintenance to get
   * @return #maintenance_existing_basic
   */
  public static function getMaintenanceBasic($id)
  {
    return self::getMaintenance($id, true);
  }

  /**
   * Get full informations for display purposes
   * @param integer $id ID of the maintenance to get
   * @return #maintenance_existing_full
   */
  public static function getMaintenanceFull($id)
  {
    return self::getMaintenance($id, false);
  }

  /**
   * Create a new maintenance
   * @param #maintenance_new $maintenance Properties of the new maintenance
   * @return @idobj ID of the created maintenance
   */
  public static function createMaintenance($maintenance)
  {
    return self::create("maintenance", [
      'product_id' => $maintenance['product']['id'],
      'name' => $maintenance['name'],
      'periodicity' => $maintenance['periodicity'],
      'outside' => $maintenance['outside'] ? 1 : 0
    ]);
  }

  /**
   * Edit basic informations of an existing maintenance
   * @param integer $id ID of the maintenance to edit
   * @param #maintenance_edit $data Properties of the maintenance to modify
   */
  public static function editMaintenance($id, $data)
  {
    return self::edit("maintenance", $id, [
      'name' => $data['name'],
      'periodicity' => $data['periodicity'],
      'outside' => $data['outside'] ? 1 : 0
    ]);
  }

  /**
   * Delete an existing maintenance
   * @param integer $id ID of the maintenance to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteMaintenance($id)
  {
    return self::delete("maintenance", $id);
  }

  /**
   * Get a list of locations and items to maintain for a given maintenance
   * This is used by ExecutionModel to build list of hardware to maintain
   * during a maintenance execution
   * @param integer $id ID of the maintenance for which to get the list
   * @return Collection of {
   *                    location: #location_existing_basic,
   * @return Collection      items: [ {
   *                               item: #item_existing_basic,
   *                               status: "NONE"
   *                           } ]
   *                  }
   */
  public static function getMaintenanceRowsToFill($id, $date)
  {

    // Build query
    $query = <<<EOF
      SELECT
        L.id AS location_id,
        I.id AS item_id,
        I.number AS item_number,
        I.compartment AS item_compartment
      FROM item I
      INNER JOIN location L ON I.location_id = L.id
      INNER JOIN product P ON I.product_id = P.id
      INNER JOIN maintenance M ON P.id = M.product_id
      WHERE M.id = ?
        AND I.deleted IS NULL
        AND I.created <= ?
EOF;

    // Execute query
    $items = self::db()->select($query, [$id, $date]);

    // Map output to correct JSON format
    return array_values(Arrays::each(
      Arrays::group(
        Arrays::each(
          $items,
          function ($item) {
            return [
              'location' => $item->location_id,
              'id' => $item->item_id,
              'number' => $item->item_number,
              'compartment' => $item->item_compartment
            ];
          }
        ),
        'location'
      ),
      function ($items, $locationId) {
        return [
          'location' => LocationModel::getLocationBasic($locationId),
          'items' => Arrays::each(
            $items,
            function ($item) {
              return [
                'item' => [
                  'id' => $item['id'],
                  'number' => $item['number'],
                  'compartment' => $item['compartment']
                ],
                'status' => ExecutionModel::STATUS_NONE
              ];
            }
          )
        ];
      }
    ));
  }

  /**
   * @internal
   * Get infos about a maintenance
   * @param integer $id ID of the maintenance to get
   * @param boolean $isBasic True to not get executions
   * @return #maintenance_existing_basic if $isBasic === true
   * @return #maintenance_existing_full if $isBasic === false
   */
  private static function getMaintenance($id, $isBasic)
  {

    // Build query
    $query = <<<EOF
      SELECT
      	M.id AS id,
      	P.id AS product_id,
      	P.name AS product_name,
        P.prefix AS product_prefix,
      	M.name AS name,
      	M.periodicity AS periodicity,
      	M.outside AS outside,
        MIN(L.execution_date) AS last
      FROM maintenance M
      INNER JOIN product P ON M.product_id = P.id
      LEFT JOIN (
        SELECT
      		I.id AS item_id,
      		M.id AS maintenance_id,
      		MAX(E.date) AS execution_date
      	FROM item I
      	INNER JOIN maintenance_exec_row R ON I.id = R.item_id
      	INNER JOIN maintenance_exec E ON R.exec_id = E.id
      	INNER JOIN maintenance M ON E.maintenance_id = M.id
      	WHERE R.success = 1
          AND I.deleted IS NULL
      	GROUP BY I.id, M.id
      	UNION
      	SELECT
      		I.id AS item_id,
      		M.id AS maintenance_id,
      		DATE('2000-01-01') AS execution_date
      	FROM item I
      	INNER JOIN product P ON I.product_id = P.id
      	INNER JOIN maintenance M ON P.id = M.product_id
      	WHERE (I.id, M.id) NOT IN (
      		SELECT
      			I.id AS item_id,
      			M.id AS maintenance_id
      		FROM item I
      		LEFT JOIN maintenance_exec_row R ON I.id = R.item_id
      		LEFT JOIN maintenance_exec E ON R.exec_id = E.id
      		LEFT JOIN maintenance M ON E.maintenance_id = M.id
      		WHERE R.success = 1
            AND I.deleted IS NULL
      		GROUP BY I.id, M.id
      	)
          AND I.deleted IS NULL
      ) L ON M.id = L.maintenance_id
      WHERE M.id = ?
      GROUP BY M.id, L.maintenance_id
EOF;

    // Execute query
    $maintenances = self::db()->select($query, [$id]);
    $count = Arrays::size($maintenances);

    // If no maintenance found, return null
    if ($count === 0) {
      return null;
    }

    // Executions and items
    $executions = [];
    $items = [];
    if (!$isBasic) {
      // Build query to find list of executions
      $query = <<<EOF
        SELECT
          E.id AS id,
          E.name AS name,
          E.date AS date,
          E.people AS people,
          E.remark AS remark,
          U.id AS user_id,
          U.name AS user_name
        FROM maintenance_exec E
        INNER JOIN user U ON E.user_id = U.id
        WHERE E.maintenance_id = ?
EOF;

      // Execute query
      $executions = self::db()->select($query, [$id]);

      // Build query to find list of items in executions
      $query = <<<EOF
      SELECT
        R.exec_id AS exec_id,
        I.id AS item_id,
        I.number AS item_number,
        I.location_id AS location_id,
        I.compartment AS item_compartment,
        R.executed AS executed,
        R.success AS success,
        R.remark AS remark
      FROM maintenance_exec E
      INNER JOIN maintenance_exec_row R ON E.id = R.exec_id
      INNER JOIN item I ON R.item_id = I.id
      WHERE E.maintenance_id = ?
EOF;

      // Execute query
      $items = self::db()->select($query, [$id]);
    }

    // Compute next maintenance
    $maintenance = Arrays::first($maintenances);
    $next = ($maintenance->last === null || $maintenance->last === '2000-01-01')
      ? null
      : new \DateTime($maintenance->last);
    if ($next !== null) {
      $next->add(new \DateInterval('P' . $maintenance->periodicity . 'M'));
      $next = $next->format('Y-m-d');
    }

    // Return first maintenance
    return Arrays::merge(
      [
        'id' => $maintenance->id,
        'product' => [
          'id' => $maintenance->product_id,
          'name' => $maintenance->product_name,
          'prefix' => $maintenance->product_prefix
        ],
        'name' => $maintenance->name,
        'periodicity' => $maintenance->periodicity,
        'outside' => $maintenance->outside === 1,
        'next' => $next
      ],
      $isBasic
      ? []
      : [
        'executions' => Arrays::each(
          $executions,
          function ($execution) use ($items) {
            return [
              'id' => $execution->id,
              'name' => $execution->name,
              'date' => $execution->date,
              'people' => $execution->people,
              'remark' => $execution->remark,
              'user' => [
                'id' => $execution->user_id,
                'name' => $execution->user_name
              ],
              'items' => array_values(Arrays::each(
                Arrays::filter(
                  $items,
                  function ($item) use (&$execution) {
                    return $item->exec_id === $execution->id;
                  }
                ),
                function ($item) {
                  return Arrays::merge(
                    [
                      'id' => $item->item_id,
                      'number' => $item->item_number,
                      'location' => LocationModel::getLocationBasic($item->location_id),
                      'compartment' => $item->item_compartment,
                      'status' => $item->executed === 0
                        ? ExecutionModel::STATUS_NONE
                        : (
                          $item->success === 1
                          ? ExecutionModel::STATUS_SUCCESS
                          : ExecutionModel::STATUS_FAILURE
                        )
                    ],
                    $item->executed === 1 && $item->success === 0
                    ? [
                      'remark' => $item->remark === null ? '' : $item->remark
                    ]
                    : []
                  );
                }
              ))
            ];
          }
        )
      ]
    );
  }
}
