<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;
use App\Models\TuyauDiametre;

class DiametreBusiness
{

  /**
   * Create a new diametre
   * @param array $diametre #diametre_new Properties of the new diametre
   * @return #idobj ID of the created diametre
   */
  public static function createDiametre($diametre)
  {
    return TuyauDiametre::create($diametre);
  }

  /**
   * Edit an existing diametre
   * @param integer $id ID of the diametre to edit
   * @param array $data #diametre_new Properties of the diametre to modify
   */
  public static function editDiametre($id, $data)
  {
    TuyauDiametre::whereId($id)->update($data);
    return TuyauDiametre::find($id);
  }

  /**
   * Delete an existing diametre
   * @param integer $id ID of the diametre to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteDiametre($id)
  {
    return TuyauDiametre::whereId($id)->delete();
  }
}
