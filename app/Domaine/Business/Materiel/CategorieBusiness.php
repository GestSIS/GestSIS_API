<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\MaterielCategorie;
use App\Models\MaterielType;
use DB;

class CategorieBusiness
{

  /**
   * Get list of categories with contained products
   * @return \Illuminate\Database\Eloquent\Collection of #categorie_existing_withproducts
   */
  public static function listCategories()
  {
    return MaterielCategorie::all();
  }

  /**
   * Create a new categorie
   * @param array $categorie #categorie_new Properties of the new categorie
   * @return #idobj ID of the created categorie
   */
  public static function createCategorie($categorie)
  {
    $order = DB::table('materiel_categories')->max('id');
    return MaterielCategorie::create([
      'designation' => $categorie['designation'] ?? '',
      'parent_id' => $categorie['parent_id'] ?? null,
      'couleur_id' => $categorie['couleur_id'],
      'tri' => ($order ?? 0) + 1,
    ]);
  }

  /**
   * Edit basic informations of an existing categorie
   * @param integer $id ID of the categorie to edit
   * @param array $data #categorie_new Properties of the categorie to modify
   */
  public static function editCategorie($id, $data)
  {
    self::assertNoCycle((int) $id, isset($data['parent_id']) ? (int) $data['parent_id'] : null);

    MaterielCategorie::whereId($id)->limit(1)->update([
      'designation' => $data['designation'],
      'parent_id' => $data['parent_id'],
      'couleur_id' => $data['couleur_id']
    ]);

    return MaterielCategorie::find($id);
  }

  /**
   * Vérifie qu'attribuer $newParentId comme parent de la catégorie $id ne créerait
   * pas de cycle dans la hiérarchie (une catégorie ne peut pas être son propre
   * ancêtre, directement ou via une chaîne de parent_id).
   */
  public static function assertNoCycle(int $id, ?int $newParentId): void
  {
    $currentId = $newParentId;
    while ($currentId !== null) {
      if ($currentId === $id) {
        throw new ArrayException([], "Récursivité illégale détectée");
      }
      $currentId = MaterielCategorie::find($currentId)?->parent_id;
    }
  }

  /**
   * Delete an existing categorie
   * @param integer $id ID of the categorie to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteCategorie($id)
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
   * Reorder an existing categorie
   * @param integer $id ID of the categorie to reorder
   * @param array $reorder #reorder Infos about the reordering
   */
  public static function reorderCategorie($id, $reorder)
  {
    // TODO: a réimplémenter
    // self::reorder("categorie", $id, $reorder);
  }
}
