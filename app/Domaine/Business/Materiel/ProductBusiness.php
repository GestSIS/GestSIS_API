<?php

namespace App\Domaine\Business\Materiel;

use App\Infrastructure\Models\MaterielType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Model for manipulating 'product' database table
 * Available public methods
 * @static listProductsBasicByCategory()
 * @static listProductsAlertsByCategory()
 * @static getProductForEditMinimal($id)
 * @static getProductForEditComplete($id)
 * @static getProductFull($id)
 * @static createProduct($product)
 * @static editProduct($id, $data)
 * @static deleteProduct($id)
 * @static reorderProduct($id, $reorder)
 */
class ProductBusiness // extends OrderModel
{

  const TYPE_NONE = "NONE";
  const TYPE_PIPE = "PIPE";
  const TYPE_BATTERY = "BATTERY";

  /**
   * Get list of products with only basic informations, grouped by category
   * @return Collection of categoryId => [ #product_existing_basic ]
   */
  public static function listProductsBasicByCategory(): Collection
  {
    return MaterielType::orderBy('tri', 'asc')->get();

    // Build query
    $query = <<<EOF
      SELECT id, name, category_id
      FROM product
      ORDER BY `order`
EOF;

    // Execute query
    $products = self::db()->select($query);

    // Map output to correct JSON format
    return Arrays::each(
      Arrays::group(
        Arrays::each(
          $products,
          function ($product) {
            return [
              'id' => $product->id,
              'name' => $product->name,
              'category_id' => $product->category_id
            ];
          }
        ),
        'category_id'
      ),
      function ($productLists) {
        return Arrays::each(
          $productLists,
          function ($product) {
            return [
              'id' => $product['id'],
              'name' => $product['name']
            ];
          }
        );
      }
    );
  }

  /**
   * Get list of products with alerts, grouped by category
   * @return Collection of #category_existing_withproductsalerts
   */
  public static function listProductsAlertsByCategory()
  {

    // Build query to retrieve categories
    $query = <<<EOF
      SELECT
        CA.id AS id,
        CA.name AS name,
        CO.id AS color_id,
        CO.name AS color_name,
        CO.foreground AS color_foreground,
        CO.background AS color_background
      FROM category CA
      INNER JOIN color CO ON CA.color_id = CO.id
      ORDER BY CA.`order`
EOF;

    // Execute query to retrieve categories
    $categories = self::db()->select($query);

    // Build query to retrieve products
    $query = <<<EOF
      SELECT
        P.id AS id,
        P.name AS name,
        P.category_id AS category_id,
        P.tocheck AS alerts_tocheck
      FROM product P
      ORDER BY P.category_id ASC, P.`order` ASC
EOF;

    // Execute query to retrieve products
    $products = self::db()->select($query);

    // Get missing items in inventory
    $missing = self::listMissingItems();

    // Get late maintenances
    $late = self::listLateMaintenances();

    // Build output
    return Arrays::each($categories, function ($category) use (&$products, &$missing, &$late) {
      return [
        'id' => $category->id,
        'name' => $category->name,
        'color' => [
          'id' => $category->color_id,
          'name' => $category->color_name,
          'foreground' => $category->color_foreground,
          'background' => $category->color_background
        ],
        'products' => Arrays::each(
          array_values(Arrays::filter(
            $products,
            function ($product) use (&$category) {
              return $product->category_id === $category->id;
            }
          )),
          function ($product) use (&$missing, &$late) {
            return [
              'id' => $product->id,
              'name' => $product->name,
              'alerts' => [
                'tocheck' => $product->alerts_tocheck > 0,
                'inventory' => $missing[$product->id] > 0,
                'maintenance' => Arrays::has($late, $product->id) && $late[$product->id] > 0
              ]
            ];
          }
        )
      ];
    });
  }

  /**
   * Get list of product IDs and missing items in inventory
   * @return [ product_id => missing items count ]
   */
  public static function listMissingItems()
  {

    // Build query
    $query = <<<EOF
    SELECT P.product_id AS id, SUM(P.missing) AS missing
    FROM (
    	SELECT I.product_id AS product_id, 1-R.found AS missing
    	FROM item I
    	INNER JOIN inventory_row R ON I.id = R.item_id
    	INNER JOIN inventory IV ON R.inventory_id = IV.id
    	INNER JOIN (
    		SELECT I.id AS item, MAX(IV.date) AS date
    		FROM item I
    		LEFT JOIN inventory_row R ON I.id = R.item_id
    		LEFT JOIN inventory IV ON R.inventory_id = IV.id
    		GROUP BY I.id
    	) SUB ON I.id = SUB.item AND IV.date = SUB.date
    	UNION ALL
    	SELECT I.product_id AS product_id, 1 AS missing
    	FROM item I
    	WHERE I.id NOT IN (
    		SELECT DISTINCT R.item_id
    		FROM inventory_row R
    	)
        AND I.deleted IS NULL
      UNION ALL
      SELECT P.id AS product_id, 0 AS missing
      FROM product P
    ) P
    GROUP BY P.product_id
EOF;

    // Execute query
    $products = self::db()->select($query);

    // Map output to correct JSON format
    $output = [];
    foreach ($products as $product) {
      $output[$product->id] = $product->missing;
    }
    return $output;
  }

  /**
   * List late maintenances
   * @return [ product_id => late maintenances count ]
   */
  public static function listLateMaintenances()
  {

    // Build query
    $query = <<<EOF
    SELECT
      M.product_id AS id,
      M.periodicity AS periodicity,
      MIN(L.execution_date) AS last
    FROM (
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
    ) L
    INNER JOIN maintenance M ON L.maintenance_id = M.id
    GROUP BY L.maintenance_id
EOF;

    // Execute query
    $products = self::db()->select($query);

    // Map output to correct JSON format
    $today = new \DateTime();
    $output = [];
    foreach ($products as $product) {
      if (!Arrays::has($output, $product->id)) {
        $output[$product->id] = 0;
      }
      if ($product->last === null || $product->last === '2000-01-01') {
        $output[$product->id] += 1;
      } else {
        $next = new \DateTime($product->last);
        $next->add(new \DateInterval('P' . $product->periodicity . 'M'));
        if ($next < $today) {
          $output[$product->id] += 1;
        }
      }
    }
    return $output;
  }

  /**
   * Get list of products by owner
   * @param integer $ownerId ID of the owner for which to retrieve products
   * @return Collection of products grouped by category
   */
  public static function listProductsByOwner($ownerId)
  {

    // Build query
    $query = <<<EOF
      SELECT
        P.id AS product_id,
        P.name AS product_name,
        CA.id AS category_id,
        CA.name AS category_name,
        CO.id AS category_color_id,
        CO.name AS category_color_name,
        CO.foreground AS category_color_foreground,
        CO.background AS category_color_background
      FROM product P
      INNER JOIN category CA ON P.category_id = CA.id
      INNER JOIN color CO ON CA.color_id = CO.id
      WHERE P.owner_id = ?
      ORDER BY CA.order ASC, P.order ASC
EOF;

    // Execute query
    $products = self::db()->select($query, [$ownerId]);
    $count = Arrays::size($products);

    // If no product found, return empty array
    if ($count === 0) {
      return array();
    }

    // Group by categories
    $categories = array();
    foreach ($products as $product) {
      $categoryKey = 'C' . $product->category_id;
      if (!array_key_exists($categoryKey, $categories)) {
        $categories[$categoryKey] = array(
          'category' => array(
            'id' => $product->category_id,
            'name' => $product->category_name,
            'color' => array(
              'id' => $product->category_color_id,
              'name' => $product->category_color_name,
              'foreground' => $product->category_color_foreground,
              'background' => $product->category_color_background
            )
          ),
          'products' => array()
        );
      }

      array_push($categories[$categoryKey]['products'], array(
        'id' => $product->product_id,
        'name' => $product->product_name
      ));
    }

    return array_values($categories);
  }

  /**
   * Get minimal infos about a product, for editing
   * @param integer $id ID of the product to retrieve
   * @return #product_existing_foredit_minimal
   */
  public static function getProductForEditMinimal($id)
  {

    // Build query
    $query = <<<EOF
      SELECT
        P.id AS id,
        P.name AS name,
        CA.id AS category_id,
        CA.name AS category_name,
        CO.id AS category_color_id,
        CO.name AS category_color_name,
        CO.foreground AS category_color_foreground,
        CO.background AS category_color_background,
        O.id AS owner_id,
        O.name AS owner_name,
        U.id AS owner_manager_id,
        U.name AS owner_manager_name
      FROM product P
      INNER JOIN category CA ON P.category_id = CA.id
      INNER JOIN color CO ON CA.color_id = CO.id
      INNER JOIN owner O ON P.owner_id = O.id
      INNER JOIN user U ON O.manager_id = U.id
      WHERE P.id = ?
EOF;

    // Execute query
    $products = self::db()->select($query, [$id]);
    $count = Arrays::size($products);

    // If no product found, return null
    if ($count === 0) {
      return null;
    }

    // Otherwise return first product
    $product = Arrays::first($products);
    return [
      'id' => $product->id,
      'name' => $product->name,
      'category' => [
        'id' => $product->category_id,
        'name' => $product->category_name,
        'color' => [
          'id' => $product->category_color_id,
          'name' => $product->category_color_name,
          'foreground' => $product->category_color_foreground,
          'background' => $product->category_color_background
        ]
      ],
      'owner' => [
        'id' => $product->owner_id,
        'name' => $product->owner_name,
        'manager' => [
          'id' => $product->owner_manager_id,
          'name' => $product->owner_manager_name
        ]
      ]
    ];
  }

  /**
   * Get basic infos about a product, for editing
   * @param integer $id ID of the product to retrieve
   * @return #product_existing_foredit_complete
   */
  public static function getProductForEditComplete($id)
  {

    // Build query
    $query = <<<EOF
      SELECT
        P.id AS id,
        P.name AS name,
        P.price AS price,
        P.provider AS provider,
        P.reparator AS reparator,
        P.prefix AS prefix,
        P.remark AS remark,
        P.tocheck AS tocheck,
        CA.id AS category_id,
        CA.name AS category_name,
        CO.id AS category_color_id,
        CO.name AS category_color_name,
        CO.foreground AS category_color_foreground,
        CO.background AS category_color_background,
        O.id AS owner_id,
        O.name AS owner_name,
        U.id AS owner_manager_id,
        U.name AS owner_manager_name,
        PP.length AS pipe_length,
        PP.separate AS pipe_separate,
        PPD.id AS pipe_diameter_id,
        PPD.diameter AS pipe_diameter_diameter,
        PB.count AS battery_count,
        PBT.id AS battery_type_id,
        PBT.name AS battery_type_name
      FROM product P
      INNER JOIN category CA ON P.category_id = CA.id
      INNER JOIN color CO ON CA.color_id = CO.id
      INNER JOIN owner O ON P.owner_id = O.id
      INNER JOIN user U ON O.manager_id = U.id
      LEFT JOIN product_pipe PP ON P.id = PP.id
      LEFT JOIN pipediameter PPD ON PP.diameter_id = PPD.id
      LEFT JOIN product_battery PB ON P.id = PB.id
      LEFT JOIN batterytype PBT ON PB.type_id = PBT.id
      WHERE P.id = ?
EOF;

    // Execute query
    $products = self::db()->select($query, [$id]);
    $count = Arrays::size($products);

    // If no product found, return null
    if ($count === 0) {
      return null;
    }

    // Otherwise return first product
    $product = Arrays::first($products);
    return [
      'id' => $product->id,
      'name' => $product->name,
      'price' => $product->price ? $product->price : "",
      'provider' => $product->provider ? $product->provider : "",
      'reparator' => $product->reparator ? $product->reparator : "",
      'prefix' => $product->prefix,
      'remark' => $product->remark ? $product->remark : "",
      'tocheck' => $product->tocheck > 0,
      'category' => [
        'id' => $product->category_id,
        'name' => $product->category_name,
        'color' => [
          'id' => $product->category_color_id,
          'name' => $product->category_color_name,
          'foreground' => $product->category_color_foreground,
          'background' => $product->category_color_background
        ]
      ],
      'owner' => [
        'id' => $product->owner_id,
        'name' => $product->owner_name,
        'manager' => [
          'id' => $product->owner_manager_id,
          'name' => $product->owner_manager_name
        ]
      ],
      'specialization' => $product->pipe_length === null
        ? (
          $product->battery_count === null
          ? [
            'type' => self::TYPE_NONE
          ]
          : [
            'type' => self::TYPE_BATTERY,
            'batteries_count' => $product->battery_count,
            'batteries_type' => [
              'id' => $product->battery_type_id,
              'name' => $product->battery_type_name
            ]
          ]
        )
        : [
          'type' => self::TYPE_PIPE,
          'pipe_length' => $product->pipe_length,
          'pipe_separate' => $product->pipe_separate === 1,
          'pipe_diameter' => [
            'id' => $product->pipe_diameter_id,
            'diameter' => $product->pipe_diameter_diameter
          ]
        ]
    ];
  }

  /**
   * Get full informations about a product
   * @param integer $id ID of the product to retrieve
   * @return #product_existing_full
   */
  public static function getProductFull($id)
  {
    // Build query
    $query = <<<EOF
      SELECT
        P.id AS id,
        P.name AS name,
        P.price AS price,
        P.provider AS provider,
        P.reparator AS reparator,
        P.prefix AS prefix,
        P.remark AS remark,
        P.tocheck AS alerts_tocheck,
        CA.id AS category_id,
        CA.name AS category_name,
        CO.id AS category_color_id,
        CO.name AS category_color_name,
        CO.foreground AS category_color_foreground,
        CO.background AS category_color_background,
        O.id AS owner_id,
        O.name AS owner_name,
        U.id AS owner_manager_id,
        U.name AS owner_manager_name,
        PP.length AS pipe_length,
        PP.separate AS pipe_separate,
        PPD.id AS pipe_diameter_id,
        PPD.diameter AS pipe_diameter_diameter,
        PB.count AS battery_count,
        PBT.id AS battery_type_id,
        PBT.name AS battery_type_name
      FROM product P
      INNER JOIN category CA ON P.category_id = CA.id
      INNER JOIN color CO ON CA.color_id = CO.id
      INNER JOIN owner O ON P.owner_id = O.id
      INNER JOIN user U ON O.manager_id = U.id
      LEFT JOIN product_pipe PP ON P.id = PP.id
      LEFT JOIN pipediameter PPD ON PP.diameter_id = PPD.id
      LEFT JOIN product_battery PB ON P.id = PB.id
      LEFT JOIN batterytype PBT ON PB.type_id = PBT.id
      WHERE P.id = ?
EOF;

    // Execute query
    $products = self::db()->select($query, [$id]);
    $count = Arrays::size($products);

    // If no product found, return null
    if ($count === 0) {
      return null;
    }

    // Get infos for inventories and maintenance alerts
    $missing = self::listMissingItems();
    $late = self::listLateMaintenances();

    // Otherwise return first product
    $product = Arrays::first($products);
    return [
      'id' => $product->id,
      'name' => $product->name,
      'price' => $product->price ? $product->price : "",
      'provider' => $product->provider ? $product->provider : "",
      'reparator' => $product->reparator ? $product->reparator : "",
      'prefix' => $product->prefix,
      'remark' => $product->remark ? $product->remark : "",
      'alerts' => [
        'tocheck' => $product->alerts_tocheck > 0,
        'inventory' => $missing[$product->id] > 0,
        'maintenance' => Arrays::has($late, $product->id) && $late[$product->id] > 0
      ],
      'category' => [
        'id' => $product->category_id,
        'name' => $product->category_name,
        'color' => [
          'id' => $product->category_color_id,
          'name' => $product->category_color_name,
          'foreground' => $product->category_color_foreground,
          'background' => $product->category_color_background
        ]
      ],
      'owner' => [
        'id' => $product->owner_id,
        'name' => $product->owner_name,
        'manager' => [
          'id' => $product->owner_manager_id,
          'name' => $product->owner_manager_name
        ]
      ],
      'specialization' => $product->pipe_length === null
        ? (
          $product->battery_count === null
          ? [
            'type' => self::TYPE_NONE
          ]
          : [
            'type' => self::TYPE_BATTERY,
            'batteries_count' => $product->battery_count,
            'batteries_type' => [
              'id' => $product->battery_type_id,
              'name' => $product->battery_type_name
            ]
          ]
        )
        : [
          'type' => self::TYPE_PIPE,
          'pipe_length' => $product->pipe_length,
          'pipe_separate' => $product->pipe_separate === 1,
          'pipe_diameter' => [
            'id' => $product->pipe_diameter_id,
            'diameter' => $product->pipe_diameter_diameter
          ]
        ],
      'items' => ItemModel::getItemsByProduct($product->id),
      'maintenances_and_controls' => Arrays::sort(
        Arrays::merge(
          MaintenanceModel::listMaintenancesNext($product->id),
          ControlModel::listControlsNext($product->id)
        ),
        function ($elem) {
          return $elem['next'] === null
            ? '2099-12-31'
            : (
              $elem['next'] === 'ASAP'
              ? '2000-01-01'
              : $elem['next']
            );
        },
        'asc'
      )
    ];
  }

  /**
   * Create a new product
   * @param #product_new $product Properties of the new product
   * @return #idobj ID of the created product
   */
  public static function createProduct($product)
  {

    // Insert into main table only
    return self::create("product", [
      'category_id' => $product['category']['id'],
      'name' => $product['name'],
      'owner_id' => $product['owner']['id'],
      'order' => self::getNextOrder("product", "category_id", $product['category']['id'])
    ]);
  }

  /**
   * Edit a product
   * @param integer $id ID of the product to edit
   * @param #product_edit_minimal | #product_edit_full $data Properties of the product to modify
   */
  public static function editProduct($id, $data)
  {

    // Simplified flags
    $isFullEdit = Arrays::has($data, 'remark');
    $isChangingCategory = Arrays::has($data, 'category');

    // If is changing category, keep order number
    $previousInfos = $isChangingCategory
      ? Arrays::first(self::db()->select("SELECT `order`, `category_id` FROM `product` WHERE id = ?", [$id]))
      : null;

    // Edit main table
    self::edit(
      "product",
      $id,
      $isFullEdit
      ? [
        'name' => $data['name'],
        'price' => $data['price'],
        'provider' => $data['provider'],
        'reparator' => $data['reparator'],
        'owner_id' => $data['owner']['id'],
        'prefix' => $data['prefix'],
        'remark' => $data['remark'],
        'tocheck' => $data['tocheck'] ? 1 : 0
      ]
      : Arrays::merge(
        [
          'name' => $data['name'],
          'owner_id' => $data['owner']['id']
        ],
        $isChangingCategory
        ? [
          'category_id' => $data['category']['id'],
          'order' => self::getNextOrder("product", "category_id", $data['category']['id'])
        ]
        : []
      )
    );

    // If is changing category, correct order numbers of other elements in previous category
    if ($isChangingCategory) {
      self::db()->update(
        "UPDATE `product` SET `order` = `order` - 1 WHERE `order` > ? AND `category_id` = ?",
        [$previousInfos->order, $previousInfos->category_id]
      );
    }

    // If full edit, proceed with specialized rows changes
    if ($isFullEdit) {

      // Delete existing specialized rows
      self::db()->delete("DELETE FROM product_pipe WHERE id = ?", [$id]);
      self::db()->delete("DELETE FROM product_battery WHERE id = ?", [$id]);

      // Create specialized row if needed
      if ($data['specialization']['type'] === self::TYPE_PIPE) {
        self::create("product_pipe", [
          'id' => $id,
          'length' => $data['specialization']['pipe_length'],
          'diameter_id' => $data['specialization']['pipe_diameter']['id'],
          'separate' => $data['specialization']['pipe_separate'] ? 1 : 0
        ]);
      } else if ($data['specialization']['type'] === self::TYPE_BATTERY) {
        self::create("product_battery", [
          'id' => $id,
          'count' => $data['specialization']['batteries_count'],
          'type_id' => $data['specialization']['batteries_type']['id']
        ]);
      }
    }
  }

  /**
   * Delete an existing product
   * @param integer $id ID of the product to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteProduct($id)
  {
    return self::delete("product", $id, "category_id");
  }

  /**
   * Reorder an existing product
   * @param integer $id ID of the product to reorder
   * @param #reorder $reorder Infos about the reordering
   */
  public static function reorderProduct($id, $reorder)
  {
    self::reorder("product", $id, $reorder, "category_id");
  }
}
