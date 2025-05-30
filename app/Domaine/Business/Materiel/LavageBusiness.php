<?php

namespace App\Domaine\Business\Materiel;

use App\Infrastructure\Models\Lavage;
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
    return Lavage::with(['article', 'article.lavages'])
      ->orderBy('date', 'desc')
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
      ->orderBy('date', 'desc')
      ->get();
  }

  /**
   * Create a new lavage
   * @param array $lavage #lavage Properties of the new lavage
   * @return # obj ID of the created lavage
   */
  public static function createLavages($lavages)
  {
    return array_map(fn($lavage) => Lavage::create($lavage), $lavages);
  }

  /**
   * Edit lavages
   * @param array $lavages
   */
  public static function editLavages($lavages)
  {
    $ids = array_map(function ($lavage) {
      Lavage::where('id', '=', $lavage['id'])->update($lavage);
      return $lavage['id'];
    }, $lavages);
    return Lavage::whereIn('id', $ids);
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
