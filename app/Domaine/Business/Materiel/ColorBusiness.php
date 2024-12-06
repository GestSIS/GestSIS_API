<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;
use App\Infrastructure\Models\Couleur;

/**
 * Model for manipulating 'color' database table
 * Available public methods
 * @static listColors()
 * @static getColor($id)
 * @static createColor($color)
 * @static editColor($id, $data)
 * @static deleteColor($id)
 */
class ColorBusiness
{  /**
   * Get list of colors
   * @return Collection of #color_existing
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
  public static function getColor($id)
  {
    return Couleur::find($id);
  }

  /**
   * Create a new color
   * @param Array $color #color_new Properties of the new color
   * @return #idobj ID of the created color
   */
  public static function createColor($color)
  {
    return Couleur::create([
      'nom' => $color['nom'],
      'foreground' => $color['foreground'],
      'background' => $color['background']
    ]);
  }

  /**
   * Edit an existing color
   * @param integer $id ID of the color to edit
   * @param Array $data #color_new Properties of the color to modify
   */
  public static function editColor($id, $data)
  {
    return Couleur::where('id', '=', $id)->update([
      'name' => $data['name'],
      'foreground' => $data['foreground'],
      'background' => $data['background']
    ]);
    return Couleur::find($id);
  }

  /**
   * Delete an existing color
   * @param integer $id ID of the color to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteColor($id)
  {
    return Couleur::where("id", '=', $id)->delete();
  }
}
