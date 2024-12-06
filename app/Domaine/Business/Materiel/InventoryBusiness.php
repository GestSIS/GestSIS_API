<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\BadRequestException;
use App\Exceptions\InternalException;
use App\Infrastructure\Models\Inventaire;

/**
 * Model for manipulating 'inventory' database table
 * Available public methods
 * @static listInventories()
 * @static getInventoryFull($id)
 * @static createInventoryPhase1($inventory, $userId)
 * @static createInventoryPhase2($inventory, $userId)
 * @static editInventory($id, $data)
 * @static deleteInventory($id)
 */
class InventoryBusiness
{
  const STATUS_NONE = "NONE";
  const STATUS_PRESENT = "PRESENT";
  const STATUS_MISSING = "MISSING";

  /**
   * Get list of inventories
   * @return Collection of #inventory_existing_basic
   */
  public static function listInventories()
  {
    return Inventaire::all();
    // Build query
    $query = <<<EOF
      SELECT id, date, title
      FROM inventory
      ORDER BY date DESC
EOF;

    // Execute query
    $inventories = self::db()->select($query);

    // Map output to correct JSON format
    return Arrays::each($inventories, function ($inventory) {
      return [
        'id' => $inventory->id,
        'date' => $inventory->date,
        'title' => $inventory->title
      ];
    });
  }

  /**
   * Get list of inventories for a given location
   * @param integer $locationId ID of the location for which to retrieve inventories
   * @return Collection of #inventory_existing_forlocation
   */
  public static function listInventoriesForLocation($locationId)
  {

    // Build query
    $query = <<<EOF
      SELECT
      	I.id AS id,
        I.date AS date,
        I.title AS title,
        I.people AS people,
        I.remark AS remark,
        U.id AS user_id,
        U.name AS user_name,
        SUM(1 - R.found) AS missing
      FROM inventory I
      INNER JOIN user U ON I.user_id = U.id
      INNER JOIN inventory_row R ON I.id = R.inventory_id
      WHERE I.location_id = ?
      GROUP BY I.id
      ORDER BY I.date DESC
EOF;

    // Execute query
    $inventories = self::db()->select($query, [$locationId]);

    // Map output to correct JSON format
    return array_values(Arrays::each(
      $inventories,
      function ($inventory) {
        return [
          'id' => $inventory->id,
          'date' => $inventory->date,
          'title' => $inventory->title,
          'user' => [
            'id' => $inventory->user_id,
            'name' => $inventory->user_name
          ],
          'people' => $inventory->people,
          'remark' => $inventory->remark,
          'missing' => intval($inventory->missing)
        ];
      }
    ));
  }

  /**
   * List inventories events for a given item
   * @param integer $itemId ID of the item for which to retrieve history
   * @return Collection of events { date, name, status, message, type, id }
   */
  public static function listInventoryEvents($itemId)
  {

    // Build query
    $query = <<<EOF
      SELECT
    	  R.found AS found,
        I.id AS id,
        I.date AS date
      FROM inventory_row R
      INNER JOIN inventory I ON R.inventory_id = I.id
      WHERE R.item_id = ?
EOF;

    // Execute query
    $inventories = self::db()->select($query, [$itemId]);

    // Map output to correct JSON format
    return array_values(Arrays::each(
      $inventories,
      function ($inventory) {
        return [
          'date' => $inventory->date,
          'name' => "Inventaire",
          'status' => $inventory->found === 1
            ? ItemModel::EVENT_STATUS_SUCCESS
            : ItemModel::EVENT_STATUS_FAILURE,
          'message' => $inventory->found === 1
            ? "Présent"
            : "Manquant",
          'type' => ItemModel::EVENT_TYPE_INVENTORY,
          'id' => $inventory->id
        ];
      }
    ));
  }

  /**
   * Get full infos about an existing inventory
   * @param integer $id ID of the inventory to retrieve
   * @return #inventory_existing_full
   */
  public static function getInventoryFull($id)
  {
    return Inventaire::find($id);
  }

  /**
   * Create an inventory, phase 1
   * @param #inventory_new $inventory Properties of the new inventory
   * @param integer $userId ID of the user who performs creation
   * @return #inventory_rows Infos to fill in phase 2
   */
  public static function createInventoryPhase1($inventory, $userId)
  {

    // Get location ID
    $locationId = $inventory['location']['id'];

    // Create inventory in database
    $idobj = self::create("inventory", [
      'date' => $inventory['date'],
      'title' => $inventory['title'],
      'people' => $inventory['people'],
      'remark' => $inventory['remark'],
      'location_id' => $locationId,
      'user_id' => $userId
    ]);

    // Get rows to fill
    $locations = ItemModel::getItemsForInventory($locationId);

    // Map output to correct JSON format
    return [
      'id' => $idobj['id'],
      'locations' => $locations
    ];
  }

  /**
   * Create an inventory, phase 2
   * @param #inventory_rows $inventory Rows to fill with inventory infos
   * @param integer $userId ID of the user who performs creation (unused)
   */
  public static function createInventoryPhase2($inventory, $userId)
  {

    // Check if rows exist for the current inventory
    $query = <<<EOF
      SELECT COUNT(*) AS `count`
      FROM inventory_row
      WHERE inventory_id = ?
EOF;
    $count = Arrays::first(self::db()->select($query, [$inventory['id']]))->count;
    if ($count > 0) {
      throw new BadRequestException(
        BadRequestException::FORBIDDEN_OPERATION,
        "L'inventaire avec l'ID {$inventory['id']} existe déjà et des lignes ne peuvent plus être ajoutées."
      );
    }

    // Create all rows according to given input
    foreach ($inventory['locations'] as $location) {
      foreach ($location['categories'] as $category) {
        foreach ($category['items'] as $item) {
          foreach ($item['item']['id'] as $idx => $itemId) {
            self::create("inventory_row", [
              'inventory_id' => $inventory['id'],
              'item_id' => $itemId,
              'found' => $idx < $item['found'] ? 1 : 0
            ]);
          }
        }
      }
    }
  }

  /**
   * Edit basic informations of an existing inventory
   * @param integer $id ID of the inventory to edit
   * @param #inventory_edit $data Properties of the inventory to modify
   */
  public static function editInventory($id, $data)
  {
    return self::edit("inventory", $id, [
      'title' => $data['title'],
      'people' => $data['people'],
      'remark' => $data['remark']
    ]);
  }

  /**
   * Delete a being-created inventory
   * @param integer $id ID of the inventory to delete
   * @return boolean true if deleted successfully
   * @throws BadRequestException::FORBIDDEN_OPERATION when trying to delete an inventory which has been fully created
   */
  public static function deleteInventory($id)
  {

    // Check if rows exist for the current inventory
    $query = <<<EOF
      SELECT COUNT(*) AS `count`
      FROM inventory_row
      WHERE inventory_id = ?
EOF;
    $count = Arrays::first(self::db()->select($query, [$id]))->count;
    if ($count > 0) {
      throw new BadRequestException(
        BadRequestException::FORBIDDEN_OPERATION,
        "L'inventaire avec l'ID $id ne peut pas être supprimé car il contient des lignes d'inventaire."
      );
    }

    // Proceed with deletion
    return self::delete("inventory", $id);
  }
}
