<?php

namespace App\Domaine\Business\Materiel;

use \Illuminate\Database\Eloquent\Collection;
use App\Infrastructure\Models\Emplacement;

/**
 * Model for manipulating 'emplacement' database table
 * Available public methods
 * @static listEmplacements()
 * @static getEmplacement($id)
 * @static createEmplacement($battery)
 * @static editEmplacement($id, $data)
 * @static deleteEmplacement($id)
 */
class EmplacementBusiness
{

  /**
   * Get list of batteries
   * @return Collection of #emplacement_existing
   */
  public static function listEmplacements()
  {
    return Emplacement::all();
  }

  /**
   * Get a single battery
   * @param integer $id ID of the battery to get
   * @return #emplacement_existing
   */
  public static function getEmplacement($id)
  {
    return Emplacement::find($id);
  }

  /**
   * Create a new battery
   * @param Array $battery #emplacement_new Properties of the new battery
   * @return #idobj ID of the created battery
   */
  public static function createEmplacement($battery)
  {

    return Emplacement::create([
      'nom' => $battery['nom']
    ]);
  }

  /**
   * Edit an existing battery
   * @param integer $id ID of the battery to edit
   * @param Array $data #emplacement_new Properties of the battery to modify
   */
  public static function editEmplacement($id, $data)
  {
    Emplacement::where('id', '=', $id)->update([
      'name' => $data['name']
    ]);
    return Emplacement::find($id);
  }

  /**
   * Delete an existing battery
   * @param integer $id ID of the battery to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteEmplacement($id)
  {
    return Emplacement::delete($id);
  }
}
