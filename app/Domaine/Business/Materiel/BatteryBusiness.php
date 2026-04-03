<?php

namespace App\Domaine\Business\Materiel;

use \Illuminate\Database\Eloquent\Collection;
use App\Models\BatterieType;

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
    return BatterieType::orderBy('nom')->get();
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
   * @param array $battery #batterytype_new Properties of the new battery
   * @return #idobj ID of the created battery
   */
  public static function createBattery($battery)
  {

    return BatterieType::create($battery);
  }

  /**
   * Edit an existing battery
   * @param integer $id ID of the battery to edit
   * @param array $data #batterytype_new Properties of the battery to modify
   */
  public static function editBattery($id, $data)
  {
    BatterieType::where('id', '=', $id)->update($data);
    return BatterieType::find($id);
  }

  /**
   * Delete an existing battery
   * @param integer $id ID of the battery to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteBattery($id)
  {
    return BatterieType::where('id', '=', $id)->delete();
  }
}
