<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\InterventionVehicule;
use App\Models\MaterielType;
use App\Models\MaterielTypeBatterie;
use App\Models\MaterielTypeTuyau;
use DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * Model for manipulating 'product' database table
 * Available public methods
 * @static listProductsBasicByCategory()
 * @static listProductsAlertsByCategory()
 * @static getProductForEditMinimal($id)
 * @static getProductForEditComplete($id)
 * @static getProductFull($id)
 * @static createProduct($product)
 * @static editProduct($id, $data)
 * @static deleteProduct($id)
 * @static reorderProduct($id, $reorder)
 */
class MaterielTypeBusiness // extends OrderModel
{

  const TYPE_NONE = 0;
  const TYPE_PIPE = 1;
  const TYPE_BATTERY = 2;
  const TYPE_VEHICULE = 3;
  const TYPE_HANGAR = 4;

  /**
   * Get list of products with only basic informations, grouped by category
   * @return Collection of categoryId => [ #product_existing_basic ]
   */
  public static function listProductsBasicByCategory(): Collection
  {
    return MaterielType::with(['tuyau', 'batterie'])->orderBy('tri', 'asc')->get();
  }

  /**
   * Create a new product
   * @param #product_new $product Properties of the new product
   * @return #idobj ID of the created product
   */
  public static function createProduct($product)
  {
    $product['fournisseur'] ??= '';
    $product['prix'] ??= '';
    $product['reparateur'] ??= '';
    $product['remarque'] ??= '';
    $product['prefix'] ??= '';

    $order = DB::table('materiel_types')->max('id');

    $tuyau = $product['tuyau'] ?? null;
    unset($product['tuyau']);
    $batterie = $product['batterie'] ?? null;
    unset($product['batterie']);

    $type = MaterielType::create([
      ...$product,
      'tri' => ($order ?? 0) + 1,
    ]);

    if ($product['type'] === self::TYPE_PIPE && $tuyau) {
      MaterielTypeTuyau::insert(['id' => $type->id, ...$tuyau]);
    }
    if ($product['type'] === self::TYPE_BATTERY && $batterie) {
      MaterielTypeBatterie::insert(['id' => $type->id, ...$batterie]);
    }

    return MaterielType::with(['tuyau', 'batterie'])->find($type->id);
  }

  /**
   * Edit a product
   * @param integer $id ID of the product to edit
   * @param #product_edit_minimal | #product_edit_full $data Properties of the product to modify
   */
  public static function editProduct($id, $data)
  {
    $data['fournisseur'] ??= '';
    $data['prix'] ??= '';
    $data['reparateur'] ??= '';
    $data['remarque'] ??= '';
    $data['prefix'] ??= '';

    $tuyau = $data['tuyau'] ?? null;
    unset($data['tuyau']);
    $batterie = $data['batterie'] ?? null;
    unset($data['batterie']);

    $oldType = MaterielType::find($id)->get(['type']);
    if (
      $oldType === self::TYPE_VEHICULE && $data['type'] !== self::TYPE_VEHICULE &&
      InterventionVehicule::join('articles', 'articles.id' . '=', 'intervention_vehicules.vehicule_id')
        ->where('articles.materiel_type_id', '=', $id)
        ->exists()
    ) {
      throw new ArrayException([], "Impossible d'enlever le type véhicule, des véhicules sont lié à ce type et utilisé pour des rapports d'intervention");
    }

    MaterielType::where('id', $id)
      ->limit(1)
      ->update($data);

    if ($data['type'] === self::TYPE_PIPE && $tuyau) {
      MaterielTypeTuyau::insert(['id' => $id, ...$tuyau]);
    } else {
      MaterielTypeTuyau::where('id', '=', $id)->delete();
    }
    if ($data['type'] === self::TYPE_BATTERY && $batterie) {
      MaterielTypeBatterie::insert(['id' => $id, ...$batterie]);
    } else {
      MaterielTypeBatterie::where('id', '=', $id)->delete();
    }

    // Gestion du changement de type en cas de véhicule

    // TODO: Avant release
    // if ($data['type'] === self::TYPE_VEHICULE) {
    //   MaterielTypeBatterie::insert(['id' => $id, ...$batterie]);
    // } else {
    //   MaterielTypeBatterie::where('id', '=', $id)->delete();
    // }

    return MaterielType::with(['tuyau', 'batterie'])->find($id);
  }

  /**
   * Delete an existing product
   * @param integer $id ID of the product to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteProduct($id)
  {
    if (
      Article::where('materiel_type_id', $id)->exists()
    ) {
      throw new ArrayException([], "Veuillez d'abord supprimer les articles");
    }
    return MaterielType::where('id', $id)->delete();
  }

  /**
   * Reorder an existing product
   * @param integer $id ID of the product to reorder
   * @param #reorder $reorder Infos about the reordering
   */
  public static function reorderProduct($id, $reorder)
  {
    // TODO: a implémenter
    // self::reorder("product", $id, $reorder, "category_id");
  }
}
