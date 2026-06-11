<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\MaterielCategorie;
use App\Models\MaterielType;
use DB;

class CategoryBusiness // extends OrderModel
{

  /**
   * Get list of categories with contained products
   * @return \Illuminate\Database\Eloquent\Collection of #category_existing_withproducts
   */
  public static function listCategories()
  {
    return MaterielCategorie::all();
  }

  /**
   * Create a new category
   * @param array $category #category_new Properties of the new category
   * @return #idobj ID of the created category
   */
  public static function createCategory($category)
  {
    $order = DB::table('materiel_categories')->max('id');
    return MaterielCategorie::create([
      'designation' => $category['designation'] ?? '',
      'parent_id' => $category['parent_id'] ?? null,
      'couleur_id' => $category['couleur_id'],
      'tri' => ($order ?? 0) + 1,
    ]);
  }

  /**
   * Edit basic informations of an existing category
   * @param integer $id ID of the category to edit
   * @param array $data #category_new Properties of the category to modify
   */
  public static function editCategory($id, $data)
  {
    if ($data['parent_id'] === $id) {
      throw new ArrayException([], "Récursivité illégale détectée");
    }

    // TODO: Controller récursivity du parent multi-niveau
    MaterielCategorie::whereId($id)->limit(1)->update([
      'designation' => $data['designation'],
      'parent_id' => $data['parent_id'],
      'couleur_id' => $data['couleur_id']
    ]);

    return MaterielCategorie::find($id);
  }

  /**
   * Delete an existing category
   * @param integer $id ID of the category to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteCategory($id)
  {
    if (
      MaterielCategorie::where('parent_id', $id)->exists() ||
      MaterielType::where('materiel_categorie_id', $id)->exists()
    ) {
      throw new ArrayException([], "Veuillez d'abord supprimer les catégories ou matériels types enfant");
    }
    return MaterielCategorie::whereId($id)->delete();
  }

  /**
   * Reorder an existing category
   * @param integer $id ID of the category to reorder
   * @param array $reorder #reorder Infos about the reordering
   */
  public static function reorderCategory($id, $reorder)
  {
    // TODO: a réimplémenter
    // self::reorder("category", $id, $reorder);
  }
}
