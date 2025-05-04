<?php

namespace App\Domaine\Business\Materiel;

use App\Infrastructure\Models\Couleur;

/**
 * Model for manipulating 'color' database table
 * Available public methods
 * @static listColors()
 * @static getCouleur($id)
 * @static createCouleur($couleur)
 * @static editCouleur($id, $data)
 * @static deleteCouleur($id)
 */
class ColorBusiness
{  /**
   * Get list of colors
   * @return \Illuminate\Database\Eloquent\Collection of #color_existing
   */
  public static function listColors()
  {
    return Couleur::all();
  }

  /**
   * Get a single color
   * @param integer $id ID of the color to get
   * @return #color_existing
   */
  public static function getCouleur($id)
  {
    return Couleur::find($id);
  }

  /**
   * Create a new color
   * @param array $couleur #color_new Properties of the new color
   * @return #idobj ID of the created color
   */
  public static function createCouleur($couleur)
  {
    return Couleur::create($couleur);
  }

  /**
   * Edit an existing color
   * @param integer $id ID of the color to edit
   * @param array $data #color_new Properties of the color to modify
   */
  public static function editCouleur($id, $data)
  {
    Couleur::where('id', '=', $id)->update($data);
    return Couleur::find($id);
  }

  /**
   * Delete an existing color
   * @param integer $id ID of the color to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteCouleur($id)
  {
    return Couleur::where("id", '=', $id)->delete();
  }
}
