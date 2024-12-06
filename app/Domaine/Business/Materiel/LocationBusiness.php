<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\BadRequestException;
use App\Exceptions\InternalException;

/**
 * Model for manipulating 'location' database table
 * Available public methods
 * @static listLocationsLinear()
 * @static listLocationsTree()
 * @static listCompartments($id)
 * @static getLocationForEdit($id)
 * @static getLocationBasic($id)
 * @static getLocationFull($id)
 * @static createLocation($location)
 * @static editLocation($id, $data)
 * @static deleteLocation($id)
 * @static reorderLocation($id, $reorder)
 */
class LocationBusiness extends OrderModel
{


  /**
   * Get list of locations in linear form, basic data only
   * @param integer $parent ID of the parent, for use by inventories
   * @return Collection of #location_existing_basic
   */
  public static function listLocationsLinear($parent = null)
  {

    // Get raw locations
    $locations = self::getLocations();

    // Helper recursive function
    $compile = function ($parent = null, $stickers = []) use (&$locations, &$compile) {

      // Local output is an array to fill
      $output = [];

      // Iterate over locations having the specified parent
      foreach ($locations as $location) {

        // Skip if not the correct parent
        if (
          ($location['parent'] === null && $parent !== null)
          ||
          ($location['parent'] !== null && $parent !== $location['parent']['id'])
        ) {
          continue;
        }

        // Improve stickers array
        $newStickers = Arrays::append($stickers, [
          'name' => $location['name'],
          'color' => [
            'foreground' => $location['color']['foreground'],
            'background' => $location['color']['background']
          ]
        ]);

        // Add this and children to output
        $output = Arrays::merge(

          // Previous output
          $output,

          // Current location
          [
            [
              'id' => $location['id'],
              'stickers' => $newStickers
            ]
          ],

          // Children
          $compile($location['id'], $newStickers)
        );
      }

      // Output
      return $output;
    };

    // Build output array
    if ($parent === null) {
      return $compile();
    } else {
      $parentLocation = self::getLocationBasic($parent);
      return Arrays::merge(
        [$parentLocation],
        $compile($parent, $parentLocation['stickers'])
      );
    }
  }

  /**
   * Get list of locations in hierarchical form, full data
   * @return Collection of #location_existing_children
   */
  public static function listLocationsTree()
  {

    // Get raw locations
    $locations = self::getLocations();

    // Helper recursive function
    $compile = function ($parent = null) use (&$locations, &$compile) {

      // Local output is an array to fill
      $output = [];

      // Iterate over locations having the specified parent
      foreach ($locations as $location) {

        // Skip if not the correct parent
        if (
          ($location['parent'] === null && $parent !== null)
          ||
          ($location['parent'] !== null && $parent !== $location['parent']['id'])
        ) {
          continue;
        }

        // Add to output
        $output = Arrays::append($output, [
          'id' => $location['id'],
          'name' => $location['name'],
          'color' => $location['color'],
          'islabeled' => $location['islabeled'] === 1,
          'pipeinspect' => $location['pipeinspect'] === 1,
          'printedinventory' => $location['printedinventory'],
          'remark' => $location['remark'],
          'children' => $compile($location['id'])
        ]);
      }

      // Output
      return $output;
    };

    // Build output array
    return $compile();
  }

  /**
   * Get list of compartments existing for a given location
   * @param integer $id ID of the location for which to find compartments
   * @return [string] Array of compartment names
   */
  public static function listCompartments($id)
  {

    // Build query
    $query = <<<EOF
      SELECT DISTINCT I.compartment
      FROM item I
      WHERE I.location_id = ?
        AND I.compartment IS NOT NULL
EOF;

    // Execute query
    $compartments = self::db()->select($query, [$id]);

    // Map output to correct JSON format
    return Arrays::each($compartments, function ($compartment) {
      return $compartment->compartment;
    });
  }

  /**
   * Get edit infos about a location
   * @param integer $id ID of the location to get
   * @return #location_existing_foredit
   */
  public static function getLocationForEdit($id)
  {

    // Build query
    $query = <<<EOF
      SELECT
        L.id AS id,
        P.name AS parent_name,
        PC.foreground AS parent_color_foreground,
        PC.background AS parent_color_background,
        L.name AS name,
        C.id AS color_id,
        C.name AS color_name,
        C.foreground AS color_foreground,
        C.background AS color_background,
        L.islabeled AS islabeled,
        L.pipeinspect AS pipeinspect,
        L.remark AS remark
      FROM location L
      INNER JOIN color C ON L.color_id = C.id
      LEFT JOIN location P ON L.parent_id = P.id
      LEFT JOIN color PC ON P.color_id = PC.id
      WHERE L.id = ?
EOF;

    // Execute query
    $locations = self::db()->select($query, [$id]);
    $count = Arrays::size($locations);

    // If no location found, return null
    if ($count === 0) {
      return null;
    }

    // Otherwise return first location
    $location = Arrays::first($locations);
    return [
      'id' => $location->id,
      'parent' => $location->parent_name === null
        ? null
        : [
          'name' => $location->parent_name,
          'color' => [
            'foreground' => $location->parent_color_foreground,
            'background' => $location->parent_color_background
          ]
        ],
      'name' => $location->name,
      'color' => [
        'id' => $location->color_id,
        'name' => $location->color_name,
        'foreground' => $location->color_foreground,
        'background' => $location->color_background
      ],
      'islabeled' => $location->islabeled === 1,
      'pipeinspect' => $location->pipeinspect === 1,
      'remark' => $location->remark
    ];
  }

  /**
   * Get basic informations for including in item descriptions
   * @param integer $id ID of the location to get
   * @return #location_existing_basic
   */
  public static function getLocationBasic($id)
  {
    return self::getLocation($id, true);
  }

  /**
   * Get full informations of a location
   * @param integer $id ID of the location to get
   * @return #location_existing_full
   */
  public static function getLocationFull($id)
  {
    return self::getLocation($id, false);
  }

  /**
   * Create a new location
   * @param #location_new $location Properties of the new location
   * @return #idobj ID of the created location
   */
  public static function createLocation($location)
  {
    $parentId = $location['parent'] === null ? null : $location['parent']['id'];
    return self::create("location", [
      'parent_id' => $parentId,
      'name' => $location['name'],
      'color_id' => $location['color']['id'],
      'islabeled' => $location['islabeled'] ? 1 : 0,
      'pipeinspect' => $location['pipeinspect'] ? 1 : 0,
      'remark' => $location['remark'],
      'order' => self::getNextOrder("location", "parent_id", $parentId)
    ]);
  }

  /**
   * @internal
   * Update location with last printed inventory date
   * @param integer $id ID of the location to update
   */
  public static function updateLastPrinted($id)
  {
    self::edit("location", $id, [
      'printedinventory' => date("Y-m-d")
    ]);
  }

  /**
   * Edit basic informations of an existing location
   * @param integer $id ID of the location to edit
   * @param #location_edit $data Properties of the location to modify
   */
  public static function editLocation($id, $data)
  {
    return self::edit("location", $id, [
      'name' => $data['name'],
      'color_id' => $data['color']['id'],
      'islabeled' => $data['islabeled'] ? 1 : 0,
      'pipeinspect' => $data['pipeinspect'] ? 1 : 0,
      'remark' => $data['remark']
    ]);
  }

  /**
   * Delete an existing location
   * @param integer $id ID of the location to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteLocation($id)
  {
    return self::delete("location", $id, "parent_id");
  }

  /**
   * Reorder an existing location
   * @param integer $id ID of the location to reorder
   * @param #reorder $reorder Reordering infos
   */
  public static function reorderLocation($id, $reorder)
  {
    self::reorder("location", $id, $reorder, "parent_id");
  }

  /**
   * @internal
   * Get full list of locations
   * @return Collection of associative result arrays
   */
  private static function getLocations()
  {

    // Build query
    $query = <<<EOF
      SELECT
        L.id AS id,
        L.name AS name,
        L.parent_id AS parent_id,
        L.islabeled AS islabeled,
        L.pipeinspect AS pipeinspect,
        L.printedinventory AS printedinventory,
        L.remark AS remark,
        C.id AS color_id,
        C.name AS color_name,
        C.foreground AS color_foreground,
        C.background AS color_background
      FROM location L
      INNER JOIN color C ON L.color_id = C.id
      ORDER BY L.`order`
EOF;

    // Execute query
    $locations = self::db()->select($query);

    // Format output
    return Arrays::each($locations, function ($location) {
      return [
        'id' => $location->id,
        'name' => $location->name,
        'parent' => $location->parent_id === null
          ? null
          : ['id' => $location->parent_id],
        'islabeled' => $location->islabeled,
        'pipeinspect' => $location->pipeinspect,
        'printedinventory' => $location->printedinventory,
        'remark' => $location->remark,
        'color' => [
          'id' => $location->color_id,
          'name' => $location->color_name,
          'foreground' => $location->color_foreground,
          'background' => $location->color_background
        ]
      ];
    });
  }

  /**
   * @internal
   * Get list of parent IDs, in order, of a given location
   * @param integer $id ID of the location for which to retrieve parents
   * @param boolean $includeSelf Include given location in result if true
   * @return [integer] List of IDs, from top level to direct parent
   */
  private static function getParents($id, $includeSelf = false)
  {

    // Build query to retrieve location
    $query = <<<EOF
      SELECT id, parent_id
      FROM location
      WHERE id = ?
EOF;

    // Helper method
    $parents = function ($id) use (&$parents, &$query) {

      // Retrieve location
      $locations = self::db()->select($query, [$id]);
      if (Arrays::size($locations) < 1) {
        throw new InternalException(
          InternalException::BAD_IMPLEMENTATION,
          "Impossible de récupérer le parent de l'emplacement avec l'ID $id."
        );
      }
      $location = Arrays::first($locations);

      // If parent is null, return empty array
      if ($location->parent_id === null) {
        return [];
      }

      // If parent is not null, add to output and call recursively
      else {
        return Arrays::append($parents($location->parent_id), $location->parent_id);
      }
    };

    // Build output
    return $includeSelf
      ? Arrays::append($parents($id), $id)
      : $parents($id);
  }

  /**
   * @internal
   * Get a linear list of all children of a given location
   * @param integer $id ID of the location for which to retrieve children
   * @return [ integer ] List of all children, linear
   */
  private static function getChildren($id)
  {

    // Build query
    $query = <<<EOF
      SELECT
        L.id AS id,
        L.parent_id AS parent
      FROM location L
EOF;

    // Execute query
    $locations = self::db()->select($query);

    // Helper function
    $appendChildren = function ($parent) use (&$locations, &$appendChildren) {
      return array_values(Arrays::flatten(Arrays::each(
        Arrays::filter(
          $locations,
          function ($location) use (&$parent) {
            return $location->parent === $parent;
          }
        ),
        function ($location) use (&$appendChildren) {
          return Arrays::merge(
            array($location->id),
            $appendChildren($location->id)
          );
        }
      )));
    };

    // Return result
    return $appendChildren($id);
  }

  /**
   * @internal
   * Get list of locations with children IDs for pipes report
   * @return { id, name, children: [ id ] }
   */
  public static function getLocationsForPipesReport()
  {

    // Build query
    $query = <<<EOF
      SELECT
        L.id AS id,
        L.parent_id AS parent,
        L.name AS name,
        L.pipeinspect AS pipeinspect
      FROM location L
EOF;

    // Execute query
    $locations = self::db()->select($query);

    // Build output
    return Arrays::each(
      Arrays::filter(
        $locations,
        function ($location) {
          return $location->pipeinspect === 1;
        }
      ),
      function ($location) {
        return [
          'id' => $location->id,
          'name' => $location->name,
          'children' => self::getChildren($location->id)
        ];
      }
    );
  }

  /**
   * @internal
   * Get content for a given location to build batteries list
   * @return [{
   *           location: {id, stickers},
   *           compartments: [{
   *             compartment: "Gauche",
   *             products: [{
   *               product: {id, name},
   *               battery: {type, count},
   *               count: 4
   *             }]
   *           }]
   *         }]
   */
  public static function getLocationForBatteries($locationId)
  {

    // Numberize $locationId
    $locationIds = '';
    if ($locationId !== 'all') {
      $locationId = intval($locationId);
      $locationIds = "AND I.location_id IN (" . Arrays::implode(
        Arrays::merge(
          array($locationId),
          self::getChildren($locationId)
        ),
        ', '
      ) . ")";
    }

    // Build query
    $query = <<<EOF
      SELECT
	      COUNT(I.id) AS items_count,
        I.location_id AS location,
        I.compartment AS compartment,
        P.id AS product_id,
        P.name AS product_name,
    	  PP.count AS batteries_count,
        B.name AS batteries_name
      FROM item I
      INNER JOIN product P ON I.product_id = P.id
      INNER JOIN product_battery PP ON P.id = PP.id
      INNER JOIN batterytype B ON PP.type_id = B.id
      WHERE I.deleted IS NULL
        $locationIds
      GROUP BY I.location_id, I.compartment, P.id, P.name, PP.count, B.name
EOF;

    // Execute query
    $products = self::db()->select($query);

    // Get locations infos (for stickers)
    $locationsStickers = self::listLocationsLinear();

    // Build output
    $output = [];
    foreach ($products as $product) {
      // Build keys
      $keyLocation = 'l' . $product->location;
      $keyCompartment = $product->compartment;

      // If location sub-array does not exist, create
      if (!Arrays::has($output, $keyLocation)) {
        $output[$keyLocation] = [
          'location' => Arrays::find(
            $locationsStickers,
            function ($location) use (&$product) {
              return $location['id'] === $product->location;
            }
          ),
          'compartments' => []
        ];
      }

      // If compartment sub-array does not exist, create
      if (!Arrays::has($output[$keyLocation]['compartments'], $keyCompartment)) {
        $output[$keyLocation]['compartments'][$keyCompartment] = [
          'compartment' => $product->compartment,
          'products' => []
        ];
      }

      // Register product
      $output[$keyLocation]['compartments'][$keyCompartment]['products'][] = [
        'product' => [
          'id' => $product->product_id,
          'name' => $product->product_name
        ],
        'battery' => [
          'type' => $product->batteries_name,
          'count' => $product->batteries_count
        ],
        'count' => $product->items_count
      ];
    }
    return array_values(
      Arrays::each(
        $output,
        function ($location) {
          return [
            'location' => $location['location'],
            'compartments' => array_values($location['compartments'])
          ];
        }
      )
    );
  }

  /**
   * @internal
   * Get locations infos associated with a given control group
   * @param integer $groupId ID of the control group
   * @return Collection of #location_existing_basic
   */
  public static function getLocationsOfControlGroup($groupId)
  {

    // Retrieve IDs of locations associated with given control group
    $query = 'SELECT location_id FROM control_group_location WHERE control_group_id = ?';
    $locations = self::db()->select($query, [$groupId]);

    // If no location found, return empty array
    if (Arrays::size($locations) === 0) {
      return [];
    }

    // Build array with infos of each location
    return array_values(
      Arrays::each(
        $locations,
        function ($location) {
          return self::getLocation($location->location_id, true);
        }
      )
    );
  }

  /**
   * @internal
   * Get informations of a location
   * @param integer $id ID of the location to get
   * @param boolean $isBasic True to get only ID and stickers
   * @return #location_existing_basic if $isBasic === true
   * @return #location_existing_full if $isBasic === false
   */
  private static function getLocation($id, $isBasic)
  {

    // Build query
    $query = <<<EOF
      SELECT
        id,
        name,
        islabeled,
        printedinventory,
        remark
      FROM location
      WHERE id = ?
EOF;

    // Execute query
    $locations = self::db()->select($query, [$id]);
    $count = Arrays::size($locations);

    // If no location found, return null
    if ($count === 0) {
      return null;
    }

    // Build query to find stickers
    $query = <<<EOF
      SELECT
        L.name AS name,
        L.islabeled AS islabeled,
        C.foreground AS color_foreground,
        C.background AS color_background
      FROM location L
      INNER JOIN color C ON L.color_id = C.id
      WHERE L.id = ?
EOF;

    // Otherwise return first location
    $location = Arrays::first($locations);
    return Arrays::merge(
      $isBasic
      ? []
      : [
        'name' => $location->name,
        'islabeled' => $location->islabeled === 1,
        'printedinventory' => $location->printedinventory,
        'remark' => $location->remark,
        'categories' => ItemModel::getItemsByLocation($location->id),
        'inventories' => InventoryModel::listInventoriesForLocation($location->id)
      ],
      [
        'id' => $location->id,
        'stickers' => array_values(Arrays::filter(
          Arrays::each(
            self::getParents($location->id, true),
            function ($id) use (&$query, &$isBasic) {

              // Find location
              $locations = self::db()->select($query, [$id]);
              if (Arrays::size($locations) !== 1) {
                throw new InternalException(
                  InternalException::BAD_IMPLEMENTATION,
                  "Impossible de trouver le parent avec l'ID $id."
                );
              }
              $location = Arrays::first($locations);

              // If not labeled, return null for further filtering
              if ($location->islabeled === 0 && !$isBasic) {
                return null;
              }

              // If labeled, return sticker
              else {
                return [
                  'name' => $location->name,
                  'color' => [
                    'foreground' => $location->color_foreground,
                    'background' => $location->color_background
                  ]
                ];
              }
            }
          ),
          function ($sticker) {
            return $sticker !== null;
          }
        ))
      ]
    );
  }
}
