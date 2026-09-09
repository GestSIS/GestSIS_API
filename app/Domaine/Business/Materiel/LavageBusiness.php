<?php

namespace App\Domaine\Business\Materiel;

use App\Models\Lavage;
use Date;

class LavageBusiness
{

  /**
   * Get list of all lavages
   * @return \Illuminate\Database\Eloquent\Collection<int, Lavage>
   */
  public static function getAllLavages()
  {
    return Lavage::with(['article', 'article.lavages', 'article.emplacementRepresentee'])
      ->orderByDesc('date')
      ->get();
  }

  /**
   * Get list of lavages since a given date
   * @param string $depuis Date à partir de laquelle récupérer les lavages
   * @return \Illuminate\Database\Eloquent\Collection<int, Lavage>
   */
  public static function getLavagesDepuis($depuis)
  {
    return Lavage::where('date', '>', $depuis)
      ->with(['article', 'article.lavages'])
      ->orderByDesc('date')
      ->get();
  }

  /**
   * Create new lavages
   * @param array $lavages Properties of each new lavage to create
   * @return Lavage[]
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
