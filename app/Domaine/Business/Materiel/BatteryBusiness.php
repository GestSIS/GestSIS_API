<?php

namespace App\Domaine\Business\Materiel;

use \Illuminate\Database\Eloquent\Collection;
use App\Infrastructure\Models\BatterieType;

/**
 * Model for manipulating 'batterytype' database table
 * Available public methods
 * @static listBatteries()
 * @static getBattery($id)
 * @static createBattery($battery)
 * @static editBattery($id, $data)
 * @static deleteBattery($id)
 */
class BatteryBusiness
{

  /**
   * Get list of batteries
   * @return Collection of #batterytype_existing
   */
  public static function listBatteries()
  {
    return BatterieType::all();
  }

  /**
   * Get a single battery
   * @param integer $id ID of the battery to get
   * @return #batterytype_existing
   */
  public static function getBattery($id)
  {
    return BatterieType::find($id);
  }

  /**
   * Create a new battery
   * @param Array $battery #batterytype_new Properties of the new battery
   * @return #idobj ID of the created battery
   */
  public static function createBattery($battery)
  {

    return BatterieType::create([
      'nom' => $battery['nom']
    ]);
  }

  /**
   * Edit an existing battery
   * @param integer $id ID of the battery to edit
   * @param Array $data #batterytype_new Properties of the battery to modify
   */
  public static function editBattery($id, $data)
  {
    BatterieType::where('id', '=', $id)->update([
      'name' => $data['name']
    ]);
    return BatterieType::find($id);
  }

  /**
   * Delete an existing battery
   * @param integer $id ID of the battery to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteBattery($id)
  {
    return BatterieType::delete($id);
  }
}
