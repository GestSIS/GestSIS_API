<?php

namespace App\Domaine\Business\Materiel;

use App\Models\Couleur;

class CouleurBusiness
{

  /**
   * Create a new couleur
   * @param array $couleur Properties of the new couleur
   * @return \App\Models\Couleur
   */
  public static function createCouleur($couleur)
  {
    return Couleur::create($couleur);
  }

  /**
   * Edit an existing couleur
   * @param integer $id ID of the couleur to edit
   * @param array $data Properties of the couleur to modify
   */
  public static function editCouleur($id, $data)
  {
    Couleur::whereId($id)->update($data);
    return Couleur::find($id);
  }

  /**
   * Delete an existing couleur
   * @param integer $id ID of the couleur to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteCouleur($id)
  {
    return Couleur::whereId($id)->delete();
  }
}
