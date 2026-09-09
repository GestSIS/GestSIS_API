<?php

namespace App\Domaine\Business\Materiel;

use \Illuminate\Database\Eloquent\Collection;
use App\Models\BatterieType;

class BatterieBusiness
{

  /**
   * Get list of batteries
   * @return Collection of #batterietype_existing
   */
  public static function listeBatteries()
  {
    return BatterieType::orderBy('nom')->get();
  }

  /**
   * Get a single batterie
   * @param integer $id ID of the batterie to get
   * @return #batterietype_existing
   */
  public static function getBatterie($id)
  {
    return BatterieType::find($id);
  }

  /**
   * Create a new batterie
   * @param array $batterie #batterietype_new Properties of the new batterie
   * @return #idobj ID of the created batterie
   */
  public static function createBatterie($batterie)
  {

    return BatterieType::create($batterie);
  }

  /**
   * Edit an existing batterie
   * @param integer $id ID of the batterie to edit
   * @param array $data #batterietype_new Properties of the batterie to modify
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
