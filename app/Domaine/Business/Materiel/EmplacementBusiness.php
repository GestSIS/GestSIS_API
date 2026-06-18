<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use DB;
use \Illuminate\Database\Eloquent\Collection;
use App\Models\Emplacement;

/**
 * Model for manipulating 'emplacement' database table
 * Available public methods
 * @static listEmplacements()
 * @static getEmplacement($id)
 * @static createEmplacement($emplacement)
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
   * Get a single emplacement
   * @param integer $id ID of the emplacement to get
   * @return #emplacement_existing
   */
  public static function getEmplacement($id)
  {
    return Emplacement::find($id);
  }

  /**
   * Create a new emplacement
   * @param array $emplacement #emplacement_new Properties of the new emplacement
   * @return #idobj ID of the created emplacement
   */
  public static function createEmplacement($emplacement)
  {
    $order = DB::table('emplacements')->max('id');
    $emplacement['remarque'] ??= '';
    $emplacement['tri'] = ($order ?? 0) + 1;
    return Emplacement::create($emplacement);
  }

  /**
   * Edit an existing emplacement
   * @param integer $id ID of the emplacement to edit
   * @param array $data #emplacement_new Properties of the emplacement to modify
   */
  public static function editEmplacement($id, $data)
  {
    $data['remarque'] ??= '';
    Emplacement::whereId($id)
      ->update($data);
    return Emplacement::find($id);
  }

  /**
   * Delete an existing emplacement
   * @param integer $id ID of the emplacement to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteEmplacement($id)
  {
    if (Article::where('emplacement_id', $id)->exists()) {
      throw new ArrayException([], "Veuillez d'abord supprimer les article de cet emplacement");
    }
    return Emplacement::whereId($id)->delete();
  }
}
