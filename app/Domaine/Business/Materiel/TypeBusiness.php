<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\MaterielType;

/**
 * Model for manipulating 'type' database table
 * Available public methods
 * @static listTypesWithProducts()
 * @static getTypeBasic($id)
 * @static createType($type)
 * @static editType($id, $data)
 * @static deleteType($id)
 * @static reorderType($id, $reorder)
 */
class TypeBusiness extends OrderModel
{

  /**
   * Get list of categories with contained products
   * @return Collection of #type_existing_withproducts
   */
  public static function listTypes()
  {
    return MaterielType::all();
  }

  /**
   * Get basic infos about a type, for editing purposes
   * @param integer $id ID of the type to retrieve
   * @return #type_existing_basic
   */
  public static function getTypeBasic($id)
  {
    return MaterielType::find($id);
  }

  /**
   * Create a new type
   * @param Array $type #type_new Properties of the new type
   * @return #idobj ID of the created type
   */
  public static function createType($type)
  {
    return MaterielType::create([
      'name' => $type['name'],
      'color_id' => $type['color_id'],
      'order' => $type['color_id'], // TODO: à implémenter
      // 'order' => self::getNextOrder("type")
    ]);
  }

  /**
   * Edit basic informations of an existing type
   * @param integer $id ID of the type to edit
   * @param Array $data #type_new Properties of the type to modify
   */
  public static function editType($id, $data)
  {
    // TODO: Controller récursivity du parent multi-niveau
    MaterielType::where('id', $id)->limit(1)->update([
      'name' => $data['name'],
      'color_id' => $data['color']['id']
    ]);

    return MaterielType::find($id);
  }

  /**
   * Delete an existing type
   * @param integer $id ID of the type to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteType($id)
  {
    return MaterielType::where('id', $id)->delete();
  }

  /**
   * Reorder an existing type
   * @param integer $id ID of the type to reorder
   * @param Array $reorder #reorder Infos about the reordering
   */
  public static function reorderType($id, $reorder)
  {
    // TODO: a réimplémenter
    // self::reorder("type", $id, $reorder);
  }
}
