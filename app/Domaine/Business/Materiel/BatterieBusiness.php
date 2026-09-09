<?php

namespace App\Domaine\Business\Materiel;

use App\Models\BatterieType;

class BatterieBusiness
{
  /**
   * Create a new batterie
   * @param array $batterie Properties of the new batterie
   * @return \App\Models\BatterieType
   */
  public static function createBatterie($batterie)
  {

    return BatterieType::create($batterie);
  }

  /**
   * Edit an existing batterie
   * @param integer $id ID of the batterie to edit
   * @param array $data Properties of the batterie to modify
   */
  public static function editBatterie($id, $data)
  {
    BatterieType::whereId($id)->update($data);
    return BatterieType::find($id);
  }

  /**
   * Delete an existing batterie
   * @param integer $id ID of the batterie to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteBatterie($id)
  {
    return BatterieType::whereId($id)->delete();
  }
}
