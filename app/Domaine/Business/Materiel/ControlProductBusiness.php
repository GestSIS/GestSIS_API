<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;

/**
 * Model for manipulating 'control_product' database table
 * Available public methods
 * @static getControlledProducts($controlId, $productId)
 * @static listControlLines($groupId)
 * @static getControlledProductsByUser($userId)
 * @static addProductToControl($productId, $controlId, $data)
 * @static editControlledProduct($productId, $controlId, $data)
 * @static removeProductFromControl($productId, $controlId)
 */
class ControlProductBusiness
{
  /**
   * Get products part of a given control
   * @param integer $controlId ID of the control to get
   * @param integer $productId optional product ID to get only that specific product
   * @return Collection|Object #control_product_forlist
   */
  public static function getControlledProducts($controlId, $productId = null)
  {

    // Indicator of the type of request
    $isOnlyOne = $productId !== null;

    // Build query
    $andWhere = $isOnlyOne ? "AND CP.product_id = ?" : "";
    $query = <<<EOF
      SELECT CP.operation AS operation, CP.contact_type AS contact_type, U.id AS user_id, U.name AS user_name, P.id AS product_id, P.name AS product_name
      FROM control_product CP
        INNER JOIN product P ON CP.product_id = P.id
        LEFT JOIN user U ON CP.contact_id = U.id
      WHERE CP.control_id = ? $andWhere
EOF;

    // Execute query
    $controlledProducts = self::db()->select($query, Arrays::merge([$controlId], $isOnlyOne ? [$productId] : []));
    $count = Arrays::size($controlledProducts);

    // If no result found, return null or []
    if ($count === 0) {
      return $isOnlyOne ? null : [];
    }

    // Function for returning one controlled product
    $formatResult = function ($controlledProduct) {
      $contactType = self::convertContactTypeToString($controlledProduct->contact_type);
      return [
        'product' => [
          'id' => $controlledProduct->product_id,
          'name' => $controlledProduct->product_name
        ],
        'operation' => $controlledProduct->operation,
        'contact' => Arrays::merge(
          [
            'type' => $contactType
          ],
          $contactType === 'SPECIFIC'
          ? [
            'user' => [
              'id' => $controlledProduct->user_id,
              'name' => $controlledProduct->user_name
            ]
          ]
          : []
        )
      ];
    };

    // Otherwise return results
    return $isOnlyOne
      ? $formatResult(Arrays::first($controlledProducts))
      : Arrays::each($controlledProducts, $formatResult);
  }

  /**
   * Get list of controls to perform, grouped by location, for a given control group (for printing)
   * @param integer $groupId ID of the control group for which to retrieve control lines
   * @return Collection with below format
   * [
   *   {
   *     location: { id, stickers }, #location_existing_basic
   *     rows: [
   *       _.extend(
   *         { product, operation, contact }, #control_product_forlist
   *         {
   *           uids: [(string)] | integer
   *         }
   *       )
   *     ]
   *   }
   * ]
   * if uids is an array of string, it's individual item numbers to show one per
   * line. If it's an integer, it's just the number of non-numbered items to
   * show as a single line.
   */
  public static function listControlLines($groupId)
  {

    // Retrieve control ID for given group
    $controlId = Arrays::first(
      self::db()->select(
        "SELECT control_id FROM control_group WHERE id = ?",
        [$groupId]
      )
    )->control_id;

    // Retrieve controlled products for given control
    $controlledProductsRaw = self::getControlledProducts($controlId);
    $controlledProducts = [];
    $controlledProductsIds = [];
    foreach ($controlledProductsRaw as $cpr) {
      $controlledProducts['p' . $cpr['product']['id']] = $cpr;
      $controlledProductsIds[] = $cpr['product']['id'];
    }

    // Retrieve managers of hardware groups associated with controlled products
    $controlledProductsIds = Arrays::implode($controlledProductsIds, ', ');
    $query = <<<EOF
      SELECT P.id AS product_id, U.name AS manager_name
      FROM product P
        INNER JOIN owner O ON P.owner_id = O.id
        INNER JOIN user U ON O.manager_id = U.id
      WHERE P.id IN ($controlledProductsIds)
EOF;
    $owners = [];
    Arrays::each(
      self::db()->select($query),
      function ($owning) use (&$owners) {
        $owners['p' . $owning->product_id] = $owning->manager_name;
      }
    );

    // Build results array
    $results = [];
    Arrays::each(
      self::db()->select(
        "SELECT location_id FROM control_group_location WHERE control_group_id = ?",
        [$groupId]
      ),
      function ($location) use (&$results, $controlledProducts, $owners) {
        Arrays::each(
          ItemModel::getItemsByLocation($location->location_id),
          function ($category) use (&$results, $controlledProducts, $owners) {
            Arrays::each(
              $category['products'],
              function ($product) use (&$results, $controlledProducts, $owners) {
                // If product does not appear in controlled products, skip
                $productKey = 'p' . $product['product']['id'];
                if (!Arrays::has($controlledProducts, $productKey)) {
                  return;
                }

                // If location does not appear yet in results, add empty structure
                $locationKey = 'l' . $product['location']['id'];
                if (!Arrays::has($results, $locationKey)) {
                  $results[$locationKey] = [
                    'location' => $product['location'],
                    'rows' => []
                  ];
                }

                // Append product details to rows
                $cpr = $controlledProducts[$productKey];
                $prefix = Arrays::has($product['product'], 'prefix')
                  ? $product['product']['prefix']
                  : null;
                $results[$locationKey]['rows'][] = [
                  'product' => $cpr['product'],
                  'operation' => $cpr['operation'],
                  'contact' => $cpr['contact']['type'] === 'OWNER'
                    ? [
                      'type' => 'OWNER',
                      'user' => [
                        'name' => $owners['p' . $cpr['product']['id']]
                      ]
                    ]
                    : $cpr['contact'],
                  'uids' => $prefix !== null
                    ? Arrays::each(
                      $product['items'],
                      function ($item) use ($prefix) {
                        return $item['number']
                          ? $prefix . $item['number']
                          : "(numéro manquant)";
                      }
                    )
                    : Arrays::size($product['items'])
                ];
              }
            );
          }
        );
      }
    );

    // Sort results by location names, combined
    return array_values(
      Arrays::sort(
        $results,
        function ($result) {
          return Arrays::implode(
            Arrays::each(
              $result['location']['stickers'],
              function ($sticker) {
                return $sticker['name'];
              }
            ),
            '_'
          );
        }
      )
    );
  }

  /**
   * List products having a specific user as contact
   * @param integer $userId ID of the user
   * @return Collection of #control_contact_foruser
   */
  public static function getControlledProductsByUser($userId)
  {

    // Build query
    $query = <<<EOF
      SELECT P.id AS product_id, P.name AS product_name,
             C.id AS control_id, C.name AS control_name
      FROM control_product CP
        INNER JOIN product P ON CP.product_id = P.id
        INNER JOIN control C ON CP.control_id = C.id
      WHERE CP.contact_type = 2
        AND CP.contact_id = ?
EOF;

    // Execute query
    $controlledProducts = self::db()->select($query, [$userId]);

    // Function for returning one result
    $formatResult = function ($controlledProduct) {
      return [
        'control' => [
          'id' => $controlledProduct->control_id,
          'name' => $controlledProduct->control_name
        ],
        'product' => [
          'id' => $controlledProduct->product_id,
          'name' => $controlledProduct->product_name
        ]
      ];
    };

    // Return results
    return Arrays::each(
      $controlledProducts,
      $formatResult
    );
  }

  /**
   * Add a product to a control with associated infos
   * @param integer $productId ID of the product to add to the control
   * @param integer $controlId ID of the control to add the product to
   * @param Array $data #control_product Properties of the controlled product
   */
  public static function addProductToControl($productId, $controlId, $data)
  {
    return self::create("control_product", [
      'control_id' => $controlId,
      'product_id' => $productId,
      'operation' => $data['operation'],
      'contact_type' => self::convertContactTypeToInt($data['contact']['type']),
      'contact_id' => $data['contact']['type'] === 'SPECIFIC'
        ? $data['contact']['user']['id']
        : null
    ]);
  }

  /**
   * Edit the modalities of a controlled product
   * @param integer $productId ID of the product to edit
   * @param integer $controlId ID of the control to edit
   * @param Array $data #control_product Properties of the controlled product
   */
  public static function editControlledProduct($productId, $controlId, $data)
  {

    // Get entity strings
    $entityPlural = static::entityPlural();
    $entityFeminine = static::entityFeminine();

    // Build query
    $query = <<<EOF
      UPDATE control_product
      SET `operation` = :operation,
          `contact_type` = :contact_type,
          `contact_id` = :contact_id
      WHERE control_id = :control_id
        AND product_id = :product_id
EOF;

    // Execute query
    $count = self::db()->update($query, [
      'control_id' => $controlId,
      'product_id' => $productId,
      'operation' => $data['operation'],
      'contact_type' => self::convertContactTypeToInt($data['contact']['type']),
      'contact_id' => $data['contact']['type'] === 'SPECIFIC'
        ? $data['contact']['user']['id']
        : null
    ]);

    // If more than one row was affected, there was a problem
    if ($count > 1) {
      throw new InternalException(
        InternalException::WRONG_DATABASE_BEHAVIOUR,
        "$count {$entityPlural} ont été modifié{$entityFeminine}s."
      );
    }
  }

  /**
   * Remove a product from a control
   * @param integer $productId ID of the product to be removed from the control
   * @param integer $controlId ID of the control from which to remove the product
   * @return boolean true if deleted successfully
   */
  public static function removeProductFromControl($productId, $controlId)
  {

    // Get entity strings
    $entityPlural = static::entityPlural();
    $entityFeminine = static::entityFeminine();

    // Build query
    $query = "DELETE FROM control_product WHERE control_id = ? AND product_id = ?";

    // Execute query
    $count = self::db()->delete($query, [$controlId, $productId]);

    // If count > 1, internal error
    if ($count > 1) {
      throw new InternalException(
        InternalException::WRONG_DATABASE_BEHAVIOUR,
        "$count {$entityPlural} ont été supprimé{$entityFeminine}s."
      );
    }

    // If count = 0, entity does not exist
    else if ($count === 0) {
      return false;
    }

    // Entity was successfully deleted
    return true;
  }
}
