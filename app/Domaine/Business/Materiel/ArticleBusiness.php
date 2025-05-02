<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Exceptions\InternalException;
use App\Exceptions\BadRequestException;
use App\Infrastructure\Models\Article;
use App\Infrastructure\Models\Emplacement;
use App\Infrastructure\Models\MaterielType;
use Illuminate\Database\Eloquent\Collection;
use Nette\Utils\Arrays;

/**
 * Model for manipulating 'item' database table
 * Available public methods
 */
class ArticleBusiness
{
  const EVENT_STATUS_NONE = "NONE";
  const EVENT_STATUS_SUCCESS = "SUCCESS";
  const EVENT_STATUS_WARNING = "WARNING";
  const EVENT_STATUS_FAILURE = "FAILURE";
  const EVENT_STATUS_INFO = "INFO";

  const EVENT_TYPE_LIFECYCLE = "LIFECYCLE";
  const EVENT_TYPE_INVENTORY = "INVENTORY";
  const EVENT_TYPE_MAINTENANCE = "MAINTENANCE";

  /**
   * Retour de matériel qui était attribué à un sapeur
   * @param mixed $date
   * @param int[] $articleIds
   * @param int $emplacementId
   * @return void
   */
  public static function retourArticles(int $emplacementId, string $date, array $articleIds): Collection
  {
    Article::whereIn('id', $articleIds)->update(['sapeur_id' => null, 'emplacement_id' => $emplacementId, 'retour' => $date]);
    return Article::whereIn('id', $articleIds)->get();
  }


  /**
   * Attribution d'articles'
   * @param int $sapeurId
   * @param string $date
   * @param int[] $articleIds
   * @return void
   */
  public static function attribuerArticles(int $sapeurId, string $date, array $articleIds): Collection
  {
    // Controle que les articles sont attribuable
    $nonAttribuable = Article::whereIn('articles.id', $articleIds)
      ->join('materiel_types', 'materiel_types.id', '=', 'articles.materiel_type_id')
      ->where('materiel_types.est_attribuable', '=', false)
      ->select(['articles.id', 'articles.materiel_type_id'])
      ->get();

    if (!$nonAttribuable->isEmpty()) {
      throw new ArrayException([$nonAttribuable], message: 'Certains articles ne sont pas attribuable');
    }

    Article::whereIn('id', $articleIds)->update([
      'sapeur_id' => $sapeurId,
      'emplacement_id' => null,
      'attribution' => $date
    ]);
    return Article::whereIn('id', $articleIds)->get();
  }

  public static function creerArticles(array $articles)
  {
    // fetch types equivalents
    $indexedTypes = MaterielType::all()->keyBy('id');

    // Controller qu'un article appartiennent soit à un sapeur soit à un emplacement
    foreach ($articles as $article) {
      if (
        ($article['sapeur_id'] === null && $article['emplacement_id'] === null) ||
        ($article['sapeur_id'] !== null && $article['emplacement_id'] !== null)
      ) {
        throw new ArrayException([], message: 'Certains articles sont à la fois assignés à un sapeur et à un emplacement');
      }

      $type = $indexedTypes[$article['materiel_type_id']];
      if (!$type->est_attribuable && $article['sapeur_id'] !== null) {
        throw new ArrayException([], message: "Article de type '$type->designation' n'est pas attribuable");
      }
    }

    // Controller numérotation correcte
    $articles = array_map(function ($article) use ($indexedTypes) {
      $type = $indexedTypes[$article['materiel_type_id']];
      return [
        'materiel_type_id' => $article['materiel_type_id'],
        'taille' => $type->est_taillee ? trim($article['taille'] ?? '') : '',
        'remarque' => $article['remarque'] ?? '',
        'emplacement_id' => $article['emplacement_id'],
        'sapeur_id' => $article['sapeur_id'],
        'attribution' => $article['sapeur_id'] === null ? null : $article['attribution'],
        'retour' => null,
        'numero' => $type->est_numerote ? $article['numero'] : '',
        'uuid' => uniqid(), // TODO: en avons-nous vraiment besoin ? Utiliser le numéro ou le UUID pour les codebar ?
        'achat' => $article['achat'] ?? '',
        'compartiment' => $article['compartiment'] ?? '',
        'est_etiquete' => $article['est_etiquete'] ?? false,
        'est_unique' => false, // TODO: Non utilisé pour le moment
      ];
    }, $articles);

    return array_map(fn($article) => Article::create($article), $articles);
  }

  /**
   * Get list of items per location for inventory
   * @param integer $locationId ID of the main location for which to retrieve items
   *                            Note: this will include sub-locations
   * @return [
   *           {
   *             location: #location_existing_basic,
   *             compartment: string,
   *             categories: [
   *                           {
   *                             category: #category_existing_basic,
   *                             items: [
   *                                      {
   *                                        product: #product_existing_basic,
   *                                        item: #item_existing_many,
   *                                        expected: integer
   *                                        [found]: NOT SET HERE
   *                                      }
   *                                    ]
   *                           }
   *                         ]
   *           }
   *         ]
   */
  public static function getItemsForInventory($locationId)
  {

    // Get all sub-locations
    $locations = Arrays::implode(
      Arrays::each(
        LocationModel::listLocationsLinear($locationId),
        function ($location) {
          return $location['id'];
        }
      ),
      ', '
    );

    // Build query
    $query = <<<EOF
      SELECT
        I.id AS item_id,
        I.number AS item_number,
        I.compartment AS item_compartment,
        P.id AS product_id,
        P.name AS product_name,
        P.prefix AS product_prefix,
        CA.id AS category_id,
        CA.name AS category_name,
        CO.id AS color_id,
        CO.name AS color_name,
        CO.foreground AS color_foreground,
        CO.background AS color_background,
        I.location_id AS location_id
      FROM item I
      INNER JOIN product P ON I.product_id = P.id
      INNER JOIN category CA ON P.category_id = CA.id
      INNER JOIN color CO ON CA.color_id = CO.id
      WHERE I.location_id IN ( $locations )
        AND I.deleted IS NULL
EOF;

    // Execute query
    $items = self::db()->select($query);

    // Build output
    $locations = [];
    foreach ($items as $item) {
      // Make sure compartment is set
      $itemCompartment = $item->item_compartment;
      if (is_null($itemCompartment)) {
        $itemCompartment = '';
      }

      // Add location sub-array if not exist
      $locationKey = strval($item->location_id) . '_' . $itemCompartment;
      if (!Arrays::has($locations, $locationKey)) {
        $locations[$locationKey] = [
          'location' => LocationModel::getLocationBasic($item->location_id),
          'compartment' => $itemCompartment,
          'categories' => []
        ];
      }

      // Add category sub-array if not exist
      if (!Arrays::has($locations[$locationKey]['categories'], $item->category_id)) {
        $locations[$locationKey]['categories'][$item->category_id] = [
          'category' => [
            'id' => $item->category_id,
            'name' => $item->category_name,
            'color' => [
              'id' => $item->color_id,
              'name' => $item->color_name,
              'foreground' => $item->color_foreground,
              'background' => $item->color_background
            ]
          ],
          'items' => []
        ];
      }

      // Build item group key
      $itemGroupKey = $item->product_id . '/' . $itemCompartment . '/' . ($item->item_number === null ? 'null' : $item->item_number);

      // If item group does not exist, create
      if (!Arrays::has($locations[$locationKey]['categories'][$item->category_id]['items'], $itemGroupKey)) {
        $locations[$locationKey]['categories'][$item->category_id]['items'][$itemGroupKey] = [
          'product' => [
            'id' => $item->product_id,
            'name' => $item->product_name,
            'prefix' => $item->product_prefix
          ],
          'item' => [
            'id' => [],
            'number' => $item->item_number,
            'compartment' => $itemCompartment
          ],
          'expected' => 0
        ];
      }

      // Add to item group
      $locations[$locationKey]['categories'][$item->category_id]['items'][$itemGroupKey]['item']['id'][] = $item->item_id;
      $locations[$locationKey]['categories'][$item->category_id]['items'][$itemGroupKey]['expected'] += 1;
    }

    // Map output to correct JSON format
    $output = array_values(Arrays::each(
      $locations,
      function ($location) {
        return [
          'location' => $location['location'],
          'compartment' => $location['compartment'],
          'categories' => array_values(Arrays::each(
            $location['categories'],
            function ($category) {
              return [
                'category' => $category['category'],
                'items' => array_values($category['items'])
              ];
            }
          ))
        ];
      }
    ));

    // Sort to have consistent order between display and print versions
    usort($output, function ($a, $b) {
      $mapToStr = function ($inventoryLocation) {
        return implode(
          ' ',
          array_map(
            function ($sticker) {
              return $sticker['name'];
            },
            $inventoryLocation['location']['stickers']
          )
        ) . ' ' . $inventoryLocation['compartment'];
      };
      return strcmp($mapToStr($a), $mapToStr($b));
    });

    return $output;
  }

  /**
   * Get list of items of a given sapeur
   * @param integer $sapeurId ID of the sapeur for which to get items
   * @return Collection of #item_existing_details
   */
  public static function getItemsBySapeur($sapeurId)
  {
    return Article::where('sapeur_id', '=', $sapeurId)->get();
  }

  /**
   * Get list of items that can be attributed
   * @return Collection of #item_existing_details
   */
  public static function getItemsAttribuable()
  {
    return Article::whereNull('sapeur_id')
      ->leftJoin('materiel_types', 'articles.materiel_type_id', '=', 'materiel_types.id')
      ->where('materiel_types.est_attribuable', '=', true)
      ->get(['articles.*']);
  }

  /**
   * Get list of all items
   * @return Collection of #item_existing_details
   */
  public static function getAllItems()
  {
    return Article::all();
  }

  /**
   * Get list of items of a given product
   * @param integer $productId ID of the product for which to get items
   * @return Collection of #item_existing_details
   */
  public static function getItemsByProduct($materielTypeId)
  {
    return Article::where('materiel_type_id', '=', $materielTypeId)->get();

    // // Get items and maintenances rows from database
    // $content = self::getItemsByWhere('product_id', $productId);
    // $items = $content['items'];
    // $maintenances = $content['maintenances'];

    // // Prepare locations array
    // $locationsList = LocationModel::listLocationsLinear();
    // $locations = Arrays::replaceKeys(
    //   $locationsList,
    //   array_values(Arrays::each(
    //     $locationsList,
    //     function ($location) {
    //       return $location['id'];
    //     }
    //   ))
    // );

    // // Build output
    // return array_values(Arrays::each(
    //   $items,
    //   function ($item) use (&$maintenances, &$locations) {
    //     return [
    //       'id' => $item->item_id,
    //       'product' => [
    //         'id' => $item->product_id,
    //         'name' => $item->product_name
    //       ],
    //       'location' => $locations[$item->location_id],
    //       'number' => $item->item_number,
    //       'islabeled' => $item->item_islabeled === 1,
    //       'isuniquelabeled' => $item->item_isuniquelabeled === 1,
    //       'compartment' => $item->item_compartment,
    //       'remark' => $item->item_remark,
    //       'created' => [
    //         'date' => $item->item_created_date,
    //         'user' => [
    //           'id' => $item->item_created_id,
    //           'name' => $item->item_created_name
    //         ]
    //       ],
    //       'deleted' => $item->item_deleted_id === null
    //         ? null
    //         : [
    //           'date' => $item->item_deleted_date,
    //           'user' => [
    //             'id' => $item->item_deleted_id,
    //             'name' => $item->item_deleted_name
    //           ]
    //         ],
    //       'inventory' => [
    //         'status' => $item->inventory_found === null
    //           ? InventoryModel::STATUS_NONE
    //           : (
    //             $item->inventory_found === 1
    //             ? InventoryModel::STATUS_PRESENT
    //             : InventoryModel::STATUS_MISSING
    //           ),
    //         'date' => $item->inventory_date
    //       ],
    //       'maintenances' => array_values(Arrays::each(
    //         Arrays::filter($maintenances, function ($maintenance) use (&$item) {
    //           return $maintenance->item_id === $item->item_id;
    //         }),
    //         function ($maintenance) {
    //           $maintenanceNext = $maintenance->maintenance_last === '2000-01-01'
    //             ? null
    //             : new \DateTime($maintenance->maintenance_last);
    //           if ($maintenanceNext !== null) {
    //             $maintenanceNext->add(new \DateInterval('P' . $maintenance->maintenance_periodicity . 'M'));
    //             $maintenanceNext = $maintenanceNext->format('Y-m-d');
    //           }
    //           $itemNext = $maintenance->item_last === null
    //             ? null
    //             : new \DateTime($maintenance->item_last);
    //           if ($itemNext !== null) {
    //             $itemNext->add(new \DateInterval('P' . $maintenance->maintenance_periodicity . 'M'));
    //             $itemNext = $itemNext->format('Y-m-d');
    //           }
    //           return [
    //             'maintenance' => [
    //               'id' => $maintenance->maintenance_id,
    //               'product' => [
    //                 'id' => $maintenance->product_id,
    //                 'name' => $maintenance->product_name
    //               ],
    //               'name' => $maintenance->maintenance_name,
    //               'periodicity' => $maintenance->maintenance_periodicity,
    //               'outside' => $maintenance->maintenance_outside === 1,
    //               'next' => $maintenanceNext
    //             ],
    //             'next' => $itemNext
    //           ];
    //         }
    //       ))
    //     ];
    //   }
    // ));
  }

  /**
   * Get list of items hierarchized by category, product and sublocation
   * @param integer $locationId ID of the main location to consider (incl. children)
   * @return Object field "categories" of #location_existing_full
   */
  public static function getItemsByLocation($locationId)
  {
    // TODO: Améliorer pour afficher également tout les sous emplacements.
    Emplacement::all();
    // TODO: Index emplacements O(n)

    $emplacementIds = [$locationId];
    // Iterate over emplacements O(n)

    return Article::whereIn('emplacement_id', $emplacementIds)->get();

    // // Get all sub-locations
    // $locationsList = LocationModel::listLocationsLinear($locationId);
    // $locationsIds = Arrays::each(
    //   $locationsList,
    //   function ($location) {
    //     return $location['id'];
    //   }
    // );
    // $locationsStr = Arrays::implode(
    //   $locationsIds,
    //   ', '
    // );

    // // Get items and maintenances rows from database
    // $content = self::getItemsByWhere('location_id', $locationsStr);
    // $items = $content['items'];
    // $maintenances = $content['maintenances'];

    // // Prepare locations array
    // $locations = Arrays::replaceKeys($locationsList, $locationsIds);

    // // Build output
    // $categories = [];
    // foreach ($items as $item) {

    //   // If item has been deleted, skip
    //   if ($item->item_deleted_id) {
    //     continue;
    //   }

    //   // Create category sub-array if not exist
    //   if (!Arrays::has($categories, $item->category_id)) {
    //     $categories[$item->category_id] = [
    //       'category' => [
    //         'id' => $item->category_id,
    //         'name' => $item->category_name,
    //         'color' => [
    //           'id' => $item->color_id,
    //           'name' => $item->color_name,
    //           'foreground' => $item->color_foreground,
    //           'background' => $item->color_background
    //         ]
    //       ],
    //       'products' => []
    //     ];
    //   }

    //   // Create product/location sub-array if not exist
    //   $keyLocationProduct = $item->location_id . '/' . $item->product_id;
    //   if (!Arrays::has($categories[$item->category_id]['products'], $keyLocationProduct)) {
    //     $categories[$item->category_id]['products'][$keyLocationProduct] = [
    //       'product' => [
    //         'id' => $item->product_id,
    //         'name' => $item->product_name,
    //         'prefix' => $item->product_prefix
    //       ],
    //       'location' => $locations[$item->location_id],
    //       'items' => []
    //     ];
    //   }

    //   // Add item to array
    //   $categories[$item->category_id]['products'][$keyLocationProduct]['items'][] = [
    //     'id' => $item->item_id,
    //     'product' => [
    //       'id' => $item->product_id,
    //       'name' => $item->product_name
    //     ],
    //     'location' => $locations[$item->location_id],
    //     'number' => $item->item_number,
    //     'islabeled' => $item->item_islabeled === 1,
    //     'isuniquelabeled' => $item->item_isuniquelabeled === 1,
    //     'compartment' => $item->item_compartment,
    //     'remark' => $item->item_remark,
    //     'created' => [
    //       'date' => $item->item_created_date,
    //       'user' => [
    //         'id' => $item->item_created_id,
    //         'name' => $item->item_created_name
    //       ]
    //     ],
    //     'deleted' => $item->item_deleted_id
    //       ? [
    //         'date' => $item->item_deleted_date,
    //         'user' => [
    //           'id' => $item->item_deleted_id,
    //           'name' => $item->item_deleted_name
    //         ]
    //       ]
    //       : null,
    //     'inventory' => [
    //       'status' => $item->inventory_found === null
    //         ? InventoryModel::STATUS_NONE
    //         : (
    //           $item->inventory_found === 1
    //           ? InventoryModel::STATUS_PRESENT
    //           : InventoryModel::STATUS_MISSING
    //         ),
    //       'date' => $item->inventory_date
    //     ],
    //     'maintenances' => array_values(Arrays::each(
    //       Arrays::filter($maintenances, function ($maintenance) use (&$item) {
    //         return $maintenance->item_id === $item->item_id;
    //       }),
    //       function ($maintenance) {
    //         $maintenanceNext = $maintenance->maintenance_last === '2000-01-01'
    //           ? null
    //           : new \DateTime($maintenance->maintenance_last);
    //         if ($maintenanceNext !== null) {
    //           $maintenanceNext->add(new \DateInterval('P' . $maintenance->maintenance_periodicity . 'M'));
    //           $maintenanceNext = $maintenanceNext->format('Y-m-d');
    //         }
    //         $itemNext = $maintenance->item_last === null
    //           ? null
    //           : new \DateTime($maintenance->item_last);
    //         if ($itemNext !== null) {
    //           $itemNext->add(new \DateInterval('P' . $maintenance->maintenance_periodicity . 'M'));
    //           $itemNext = $itemNext->format('Y-m-d');
    //         }
    //         return [
    //           'maintenance' => [
    //             'id' => $maintenance->maintenance_id,
    //             'product' => [
    //               'id' => $maintenance->product_id,
    //               'name' => $maintenance->product_name
    //             ],
    //             'name' => $maintenance->maintenance_name,
    //             'periodicity' => $maintenance->maintenance_periodicity,
    //             'outside' => $maintenance->maintenance_outside === 1,
    //             'next' => $maintenanceNext
    //           ],
    //           'next' => $itemNext
    //         ];
    //       }
    //     ))
    //   ];
    // }

    // // Map output to correct JSON format
    // return array_values(Arrays::each(
    //   $categories,
    //   function ($category) {
    //     return [
    //       'category' => $category['category'],
    //       'products' => array_values(Arrays::each(
    //         $category['products'],
    //         function ($product) {
    //           return [
    //             'product' => $product['product'],
    //             'location' => $product['location'],
    //             'items' => array_values($product['items'])
    //           ];
    //         }
    //       ))
    //     ];
    //   }
    // ));
  }

  /**
   * Get list of items hierarchized by category, product and sublocation
   * @param string $field Name of the database field of table item.*
   * @param string $values List of values that the database field must have
   * @return Object {
   *   items: List of items
   *   maintenances: List of maintenances
   * }
   */
  private static function getItemsByWhere($field, $values)
  {

    // Build query to get all items
    $query = <<<EOF
      SELECT
        CA.id AS category_id,
        CA.name AS category_name,
        CO.id AS color_id,
        CO.name AS color_name,
        CO.foreground AS color_foreground,
        CO.background AS color_background,
        P.id AS product_id,
        P.name AS product_name,
        P.prefix AS product_prefix,
        I.location_id AS location_id,
        I.id AS item_id,
        I.number AS item_number,
        I.islabeled AS item_islabeled,
        I.isuniquelabeled AS item_isuniquelabeled,
        I.compartment AS item_compartment,
        I.remark AS item_remark,
        I.created AS item_created_date,
        UC.id AS item_created_id,
        UC.name AS item_created_name,
        I.deleted AS item_deleted_date,
        UD.id AS item_deleted_id,
        UD.name AS item_deleted_name,
        IV.found AS inventory_found,
        IV.date AS inventory_date
      FROM item I
      INNER JOIN product P ON I.product_id = P.id
      INNER JOIN category CA ON P.category_id = CA.id
      INNER JOIN color CO ON CA.color_id = CO.id
      INNER JOIN user UC ON I.created_user_id = UC.id
      LEFT JOIN user UD ON I.deleted_user_id = UD.id
      INNER JOIN (
        SELECT I.id AS item_id, R.found AS found, IV.date AS date
        FROM item I
        INNER JOIN inventory_row R ON I.id = R.item_id
        INNER JOIN inventory IV ON R.inventory_id = IV.id
        INNER JOIN (
          SELECT I.id AS item, MAX(IV.date) AS date
          FROM item I
          LEFT JOIN inventory_row R ON I.id = R.item_id
          LEFT JOIN inventory IV ON R.inventory_id = IV.id
          WHERE I.{$field} IN ( {$values} )
          GROUP BY I.id
        ) SUB ON I.id = SUB.item AND IV.date = SUB.date
        UNION
        SELECT I.id AS item, NULL AS found, NULL AS date
        FROM item I
        WHERE I.{$field} IN ( {$values} )
          AND I.id NOT IN (
            SELECT DISTINCT R.item_id
            FROM inventory_row R
          )
      ) IV ON I.id = IV.item_id
      ORDER BY CA.`order` ASC, P.`order` ASC, I.created ASC
EOF;

    // Execute query
    $items = self::db()->select($query);

    // Build query to get item maintenance status
    $query = <<<EOF
      SELECT
      	I.id AS item_id,
        P.id AS product_id,
        P.name AS product_name,
        M.id AS maintenance_id,
        M.name AS maintenance_name,
        M.periodicity AS maintenance_periodicity,
        M.outside AS maintenance_outside,
        L2.maintenance_last AS maintenance_last,
        L.execution_date AS item_last
      FROM item I
      INNER JOIN product P ON I.product_id = P.id
      INNER JOIN maintenance M ON P.id = M.product_id
      LEFT JOIN maintenance_exec E ON M.id = E.maintenance_id
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
      	GROUP BY I.id, M.id
      ) L ON I.id = L.item_id AND M.id = L.maintenance_id
      LEFT JOIN (
      	SELECT
      		L.maintenance_id AS maintenance_id,
      		MIN(L.execution_date) AS maintenance_last
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
      			GROUP BY I.id, M.id
      		)
      	) L
      	GROUP BY L.maintenance_id
      ) L2 ON M.id = L2.maintenance_id
      WHERE I.{$field} IN ( {$values} )
      GROUP BY I.id, M.id, L2.maintenance_id
EOF;

    // Execute query
    $maintenances = self::db()->select($query);

    // Return result
    return [
      'items' => $items,
      'maintenances' => $maintenances
    ];
  }

  /**
   * Get informations about an item
   * @param integer $id ID of the item to retrieve
   * @param boolean $withHistory True to include history of the item in response
   * @return #item_existing_history
   */
  public static function getItemHistory($id, $withHistory)
  {

    // Build query
    $query = <<<EOF
      SELECT
        I.id AS id,
        P.id AS product_id,
        P.name AS product_name,
        P.prefix AS product_prefix,
        I.location_id AS location_id,
        I.number AS number,
        I.islabeled AS islabeled,
        I.isuniquelabeled AS isuniquelabeled,
        I.compartment AS compartment,
        I.remark AS remark,
        I.created AS created_date,
        UC.name AS created_user,
        I.deleted AS deleted_date,
        UD.name AS deleted_user
      FROM item I
      INNER JOIN product P ON I.product_id = P.id
      INNER JOIN user UC ON I.created_user_id = UC.id
      LEFT JOIN user UD ON I.deleted_user_id = UD.id
      WHERE I.id = ?
EOF;

    // Execute query
    $items = self::db()->select($query, [$id]);
    $count = Arrays::size($items);

    // If no item found, return null
    if ($count === 0) {
      return null;
    }

    // Get item
    $item = Arrays::first($items);

    // Get location infos
    $location = LocationModel::getLocationBasic($item->location_id);

    // Get history events
    $inventoryEvents = $withHistory
      ? InventoryModel::listInventoryEvents($item->id)
      : [];
    $maintenanceEvents = $withHistory
      ? MaintenanceModel::listMaintenanceEvents($item->id)
      : [];
    $existenceEvents = $withHistory
      ? Arrays::merge(
        [
          [
            'date' => $item->created_date,
            'name' => "Ajouté",
            'status' => ItemModel::EVENT_STATUS_INFO,
            'message' => "Par {$item->created_user}",
            'type' => ItemModel::EVENT_TYPE_LIFECYCLE,
            'id' => null
          ]
        ],
        $item->deleted_date === null
        ? []
        : [
          [
            'date' => $item->deleted_date,
            'name' => "Supprimé",
            'status' => ItemModel::EVENT_STATUS_INFO,
            'message' => "Par {$item->deleted_user}",
            'type' => ItemModel::EVENT_TYPE_LIFECYCLE,
            'id' => null
          ]
        ]
      )
      : [];

    // Output
    return Arrays::merge(
      [
        'id' => $item->id,
        'product' => [
          'id' => $item->product_id,
          'name' => $item->product_name
        ],
        'location' => $location,
        'number' => (
          $withHistory
          ? (
            $item->product_prefix
            ? $item->product_prefix . $item->number
            : null
          )
          : $item->number
        ),
        'islabeled' => $item->islabeled === 1,
        'isuniquelabeled' => $item->isuniquelabeled === 1,
        'compartment' => $item->compartment,
        'remark' => $item->remark ? $item->remark : ""
      ],
      $withHistory
      ? [
        'history' => [
          'events' => Arrays::sort(
            Arrays::merge(
              array_values($existenceEvents),
              array_values($maintenanceEvents),
              array_values($inventoryEvents)
            ),
            function ($event) {
              return $event['date'];
            },
            'asc'
          ),
          'maintenances' => MaintenanceModel::listMaintenanceByItem($item->id)
        ]
      ]
      : []
    );
  }

  // /**
  //  * Create one or more new item(s)
  //  * @param #item_new $item Properties of the item(s) to create
  //  * @param integer $userId ID of the user who performs creation
  //  * @return #item.post.output List of IDs of the created product(s)
  //  */
  // public static function createItems($artciles, $userId)
  // {

  //   $base = [];

  //   foreach ($articles as $materiel) {
  //     if ($materiel['materiel']['quantite'] ?? null != null) {
  //       $generique = new MaterielGenerique();
  //       $generique->quantite = $materiel['materiel']['quantite'];
  //       $generique->save();

  //       array_push($base, [
  //         'materiel_type_id' => $materiel['materiel_type_id'],
  //         'materiel_type' => MaterielGenerique::class,
  //         'materiel_id' => $generique->id,
  //         'taille' => trim($materiel['taille'] ?? ''),
  //         'remarque' => $materiel['remarque'] ?? '',
  //         'sapeur_id' => null,
  //         'attribution' => null,
  //         'retour' => null,
  //       ]);
  //     } else {
  //       $nominal = new MaterielNominal();
  //       $nominal->numero = $materiel['materiel']['numero'];
  //       $nominal->achat = $materiel['materiel']['achat'] ?? '';
  //       $nominal->uuid = uniqid($materiel['materiel_type_id'] . "-");
  //       $nominal->save();

  //       array_push($base, [
  //         'materiel_type_id' => $materiel['materiel_type_id'],
  //         'materiel_type' => MaterielNominal::class,
  //         'materiel_id' => $nominal->id,
  //         'taille' => trim($materiel['taille'] ?? ''),
  //         'remarque' => $materiel['remarque'] ?? '',
  //         'sapeur_id' => null,
  //         'attribution' => null,
  //         'retour' => null,
  //       ]);
  //     }
  //   }

  //   MaterielPersonnel::insert($base);


  //   // Determine if uniquely labeled
  //   $isUniqueLabeled = self::isUniqueLabeled($item['product']['id'], null);

  //   // If product does not use unique number, and number or isuniquelabeled is set, error
  //   if (
  //     !$isUniqueLabeled
  //     &&
  //     (
  //       Arrays::has($item, 'number')
  //       ||
  //       Arrays::has($item, 'isuniquelabeled')
  //     )
  //   ) {
  //     throw new BadRequestException(
  //       BadRequestException::FORBIDDEN_OPERATION,
  //       "Le matériel n'est pas étiquetté individuellement : les champs number et isuniquelabeled ne doivent pas être définis."
  //     );
  //   }

  //   // If product uses unique number and number or isuniquelabeled is not set, or quantity > 1, error
  //   if (
  //     $isUniqueLabeled
  //     &&
  //     (
  //       !Arrays::has($item, 'number')
  //       ||
  //       !Arrays::has($item, 'isuniquelabeled')
  //       ||
  //       $item['quantity'] !== 1
  //     )
  //   ) {
  //     throw new BadRequestException(
  //       BadRequestException::FORBIDDEN_OPERATION,
  //       "Le matériel est étiquetté individuellement : les champs number et isuniquelabeled doivent être définis et quantity doit être égal à 1."
  //     );
  //   }

  //   // Build input
  //   $input = Arrays::merge(
  //     [
  //       'product_id' => $item['product']['id'],
  //       'location_id' => $item['location']['id'],
  //       'islabeled' => $item['islabeled'] ? 1 : 0,
  //       'compartment' => $item['compartment'],
  //       'remark' => $item['remark'],
  //       'created' => date('Y-m-d'),
  //       'created_user_id' => $userId
  //     ],
  //     $item['quantity'] === 1 && $isUniqueLabeled
  //     ? [
  //       'number' => $item['number'],
  //       'isuniquelabeled' => $item['isuniquelabeled'] ? 1 : 0
  //     ]
  //     : [
  //       'number' => null,
  //       'isuniquelabeled' => 0
  //     ]
  //   );

  //   // Insert row(s)
  //   $ids = [];
  //   for ($i = 0; $i < $item['quantity']; $i++) {
  //     $ids = Arrays::append($ids, self::create("item", $input));
  //   }

  //   // Format output
  //   return [
  //     'ids' => Arrays::each($ids, function ($id) {
  //       return $id['id'];
  //     })
  //   ];
  // }

  /**
   * Edit an existing item
   * @param integer $id ID of the item to edit
   * @param #item_edit $data Properties of the item to modify
   */
  public static function editItem($id, $data)
  {

    // Determine if is uniquely labeled
    $isUniqueLabeled = self::isUniqueLabeled(null, $id);

    // If product does not use unique number, and number or isuniquelabeled is set, error
    if (
      !$isUniqueLabeled
      &&
      (
        Arrays::has($data, 'number')
        ||
        Arrays::has($data, 'isuniquelabeled')
      )
    ) {
      throw new BadRequestException(
        BadRequestException::FORBIDDEN_OPERATION,
        "Le matériel n'est pas étiquetté individuellement : les champs number et isuniquelabeled ne doivent pas être définis."
      );
    }

    // If product uses unique number and number or isuniquelabeled is not set, or quantity > 1, error
    if (
      $isUniqueLabeled
      &&
      (
        !Arrays::has($data, 'number')
        ||
        !Arrays::has($data, 'isuniquelabeled')
      )
    ) {
      throw new BadRequestException(
        BadRequestException::FORBIDDEN_OPERATION,
        "Le matériel est étiquetté individuellement : les champs number et isuniquelabeled doivent être définis."
      );
    }

    // Proceed with edition
    self::edit("item", $id, Arrays::merge(
      [
        'location_id' => $data['location']['id'],
        'islabeled' => $data['islabeled'] ? 1 : 0,
        'compartment' => $data['compartment'],
        'remark' => $data['remark']
      ],
      $isUniqueLabeled
      ? [
        'number' => $data['number'],
        'isuniquelabeled' => $data['isuniquelabeled'] ? 1 : 0
      ]
      : []
    ));
  }

  /**
   * Delete an existing item (virtually)
   * @param integer $id ID of the item to delete
   * @param integer $id ID of the user who performs deletion
   */
  public static function deleteVirtuallyItem($id, $userId)
  {
    // TODO: à implémenter !

    // Make sure it is not already registered as deleted
    $items = self::db()->select("SELECT deleted FROM item WHERE id = ?", [$id]);
    if (Arrays::size($items) === 0) {
      throw new BadRequestException(
        BadRequestException::RESOURCE_NOT_FOUND,
        "La pièce avec l'ID $id n'existe pas."
      );
    }
    if (Arrays::first($items)->deleted !== null) {
      throw new BadRequestException(
        BadRequestException::RESOURCE_NOT_FOUND,
        "La pièce avec l'ID $id est déjà enregistrée comme supprimée."
      );
    }

    // Proceed with virtual deletion
    self::edit("item", $id, [
      'deleted' => date('Y-m-d'),
      'deleted_user_id' => $userId
    ]);
  }

  /**
   * @internal
   * Determine if the product of an item is uniquely labeled
   * @param integer $productId ID of the product to get
   * @param integer $itemId ID of the item for which to get product
   * @return boolean true if uniquely labeled
   */
  private static function isUniqueLabeled($productId, $itemId)
  {

    // Determine if querying based on product ID or item ID
    $isQueryByProduct = $productId !== null;

    // Build query
    $condition = $isQueryByProduct
      ? "WHERE P.id = ?"
      : "INNER JOIN item I ON P.id = I.product_id WHERE I.id = ?";
    $query = <<<EOF
      SELECT P.prefix
      FROM product P
      $condition
EOF;

    // Execute query
    $products = self::db()->select($query, [$isQueryByProduct ? $productId : $itemId]);

    // If result is > 1, error
    if (Arrays::size($products) !== 1) {
      throw new InternalException(
        InternalException::BAD_IMPLEMENTATION,
        "Impossible de récupérer le champ prefix d'un produit."
      );
    }

    // Return result
    return Arrays::first($products)->prefix !== null;
  }
}
