<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;
use App\Models\TuyauDiametre;

/**
 * Model for manipulating 'diameter' database table
 * Available public methods
 * @static listDiameters()
 * @static getDiameter($id)
 * @static createDiameter($diameter)
 * @static editDiameter($id, $data)
 * @static deleteDiameter($id)
 */
class DiameterBusiness
{
  /**
   * Get list of diameters
   * @return \Illuminate\Database\Eloquent\Collection of #diameter_existing
   */
  public static function listDiameters()
  {
    return TuyauDiametre::orderBy('diametre')->get();
  }

  /**
   * Get a single diameter
   * @param integer $id ID of the diameter to get
   * @return #diameter_existing
   */
  public static function getDiameter($id)
  {
    TuyauDiametre::find($id);
  }

  /**
   * Create a new diameter
   * @param array $diameter #diameter_new Properties of the new diameter
   * @return #idobj ID of the created diameter
   */
  public static function createDiameter($diameter)
  {
    return TuyauDiametre::create($diameter);
  }

  /**
   * Edit an existing diameter
   * @param integer $id ID of the diameter to edit
   * @param array $data #diameter_new Properties of the diameter to modify
   */
  public static function editDiameter($id, $data)
  {
    TuyauDiametre::whereId($id)->update($data);
    return TuyauDiametre::find($id);
  }

  /**
   * Delete an existing diameter
   * @param integer $id ID of the diameter to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteDiameter($id)
  {
    return TuyauDiametre::whereId($id)->delete();
  }
}
