<?php

namespace App\Domaine\Business\Materiel;

use App\Models\Lavage;
use Date;

/**
 * Model for manipulating 'lavagetype' database table
 * Available public methods
 * @static getlavage($id)
 * @static createLavage($lavage)
 * @static editLavage($id, $data)
 * @static deleteLavage($id)
 */
class LavageBusiness
{

  /**
   * Get a single lavage
   * @return
   */
  public static function getAllLavages()
  {
    return Lavage::with(['article', 'article.lavages', 'article.emplacementRepresentee'])
      ->orderByDesc('date')
      ->get();
  }

  /**
   * Get derniers lavages
   * @return
   */
  public static function getLavagesDepuis($depuis)
  {
    return Lavage::where('date', '>', $depuis)
      ->with(['article', 'article.lavages'])
      ->orderByDesc('date')
      ->get();
  }

  /**
   * Create a new lavage
   * @param array $lavage #lavage Properties of the new lavage
   * @return # obj ID of the created lavage
   */
  public static function createLavages($lavages)
  {
    return collect($lavages)->map(Lavage::create(...))->all();
  }

  /**
   * Edit lavages
   * @param array $lavages
   */
  public static function editLavages($lavages)
  {
    $ids = collect($lavages)->map(function ($lavage) {
      Lavage::whereId($lavage['id'])->update($lavage);
      return $lavage['id'];
    })->all();
    return Lavage::whereIn('id', $ids)->get();
  }

  /**
   * Delete an existing lavage
   * @param integer $id ID of the lavage to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteLavages($ids)
  {
    return Lavage::whereIn('id', $ids)->delete();
  }
}
