<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Controle;
use App\Models\InterventionVehicule;
use App\Models\MaterielType;
use App\Models\MaterielTypeBatterie;
use App\Models\MaterielTypeTuyau;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use DB;

class MaterielTypeBusiness
{

  const TYPE_NONE = 0;
  const TYPE_TUYAU = 1;
  // 2 = ancien TYPE_BATTERY, retiré (migré vers la propriété a_batterie), ne pas réutiliser
  const TYPE_VEHICULE = 3;
  const TYPE_HANGAR = 4;

  /**
   * Create a new product
   * @param array $product Properties of the new product
   * @return \App\Models\MaterielType
   */
  public static function createProduct($product)
  {
    $product['fournisseur'] ??= '';
    $product['numero_fournisseur'] ??= '';
    $product['prix'] ??= '';
    $product['reparateur'] ??= '';
    $product['remarque'] ??= '';
    $product['prefix'] ??= '';
    $product['est_perimable'] ??= false;
    $product['type'] = (int) $product['type'];
    $product['est_emplacement'] = $product['type'] === self::TYPE_VEHICULE;
    if ($product['est_emplacement']) {
      // Un véhicule n'est ni attribuable à un sapeur, ni taillé, mais toujours
      // numéroté (plaque, numéro de véhicule…) pour pouvoir être contrôlé.
      $product['est_attribuable'] = false;
      $product['est_taillee'] = false;
      $product['est_numerote'] = true;
    }

    $order = DB::table('materiel_types')->max('id');

    $tuyau = $product['tuyau'] ?? null;
    unset($product['tuyau']);
    $batterie = $product['batterie'] ?? null;
    unset($product['batterie']);

    $type = MaterielType::create([
      ...$product,
      'tri' => ($order ?? 0) + 1,
    ]);

    if ($product['type'] === self::TYPE_TUYAU && $tuyau) {
      MaterielTypeTuyau::create(['id' => $type->id, ...$tuyau]);
    }
    if (($product['a_batterie'] ?? false) && $batterie) {
      MaterielTypeBatterie::create(['id' => $type->id, ...$batterie]);
    }

    return MaterielType::with(['tuyau', 'batterie'])->find($type->id);
  }

  /**
   * Edit a product
   * @param integer $id ID of the product to edit
   * @param array $data Properties of the product to modify
   */
  public static function editProduct($id, $data)
  {
    // Type supprimé entre-temps (autre onglet, autre utilisateur) : 404 plutôt qu'une 500.
    $oldType = (int) MaterielType::findOrFail($id)->type;

    $data['fournisseur'] ??= '';
    $data['numero_fournisseur'] ??= '';
    $data['prix'] ??= '';
    $data['reparateur'] ??= '';
    $data['remarque'] ??= '';
    $data['prefix'] ??= '';
    $data['est_perimable'] ??= false;
    $data['type'] = (int) $data['type'];
    $data['est_emplacement'] = $data['type'] === self::TYPE_VEHICULE;
    if ($data['est_emplacement']) {
      // Un véhicule n'est ni attribuable à un sapeur, ni taillé, mais toujours
      // numéroté (plaque, numéro de véhicule…) pour pouvoir être contrôlé.
      $data['est_attribuable'] = false;
      $data['est_taillee'] = false;
      $data['est_numerote'] = true;
    }

    if (!$data['est_numerote']) {
      $controles = Controle::whereHas('materielTypes', fn (Builder $query) => $query->where('materiel_type_id', $id))
        ->pluck('nom');
      if ($controles->isNotEmpty()) {
        throw new ArrayException([], "Impossible de retirer la numérotation : ce type est contrôlé ({$controles->join(', ')})");
      }
    }

    $tuyau = $data['tuyau'] ?? null;
    unset($data['tuyau']);
    $batterie = $data['batterie'] ?? null;
    unset($data['batterie']);

    if (
      $oldType === self::TYPE_VEHICULE && $data['type'] !== self::TYPE_VEHICULE &&
      InterventionVehicule::join('articles', 'articles.id', '=', 'intervention_vehicule.vehicule_id')
        ->where('articles.materiel_type_id', $id)
        ->exists()
    ) {
      throw new ArrayException([], "Impossible d'enlever le type véhicule, des véhicules sont lié à ce type et utilisé pour des rapports d'intervention");
    }

    MaterielType::whereId($id)
      ->limit(1)
      ->update($data);

    if ($data['type'] === self::TYPE_TUYAU && $tuyau) {
      MaterielTypeTuyau::updateOrCreate(['id' => $id], $tuyau);
    } else {
      MaterielTypeTuyau::whereId($id)->delete();
    }
    if (($data['a_batterie'] ?? false) && $batterie) {
      MaterielTypeBatterie::updateOrCreate(['id' => $id], $batterie);
    } else {
      MaterielTypeBatterie::whereId($id)->delete();
    }

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
    return MaterielType::whereId($id)->delete();
  }

  /**
   * Reorder an existing product
   * @param integer $id ID of the product to reorder
   * @param array $reorder Infos about the reordering
   */
  public static function reorderProduct($id, $reorder)
  {
    // TODO: a implémenter
    // self::reorder("product", $id, $reorder, "categorie_id");
  }

  /**
   * Pour chaque type de matériel périmable, compte les articles actifs déjà
   * périmés (date de péremption atteinte). Les types sans article périmé
   * renvoient un compteur à zéro.
   *
   * @return array<int, array{materiel_type_id: int, nb_perimes: int}>
   */
  public static function calculerStatutsPeremption(): array
  {
    $typeIds = MaterielType::where('est_perimable', true)->pluck('id');

    if ($typeIds->isEmpty()) {
      return [];
    }

    $nbParType = self::articlesPerimesQuery($typeIds)
      ->selectRaw('materiel_type_id, COUNT(*) as nb_perimes')
      ->groupBy('materiel_type_id')
      ->pluck('nb_perimes', 'materiel_type_id');

    return $typeIds->map(fn (int $typeId): array => [
      'materiel_type_id' => $typeId,
      'nb_perimes'       => (int) $nbParType->get($typeId, 0),
    ])->values()->all();
  }

  /**
   * Détail des articles actifs déjà périmés, groupés par type de matériel périmable.
   * Ne renvoie que les types ayant au moins un article périmé.
   *
   * @return array<int, array{materiel_type_id: int, designation: string, articles: array<int, array>}>
   */
  public static function getArticlesPerimes(): array
  {
    $types = MaterielType::where('est_perimable', true)->get();

    if ($types->isEmpty()) {
      return [];
    }

    $articlesParType = self::articlesPerimesQuery($types->pluck('id'))
      ->with(['sapeur', 'emplacement'])
      ->get()
      ->groupBy('materiel_type_id');

    return $types->map(function (MaterielType $type) use ($articlesParType): ?array {
      $articles = $articlesParType->get($type->id);

      if ($articles === null || $articles->isEmpty()) {
        return null;
      }

      return [
        'materiel_type_id' => $type->id,
        'designation'      => $type->designation,
        'articles'         => $articles->map(fn (Article $article): array => [
          'id'              => $article->id,
          'numero'          => $article->numero,
          'designation'     => $article->designation,
          'date_peremption' => $article->date_peremption->toDateString(),
          'sapeur'          => $article->sapeur ? "{$article->sapeur->nom} {$article->sapeur->prenom}" : null,
          'emplacement'     => $article->emplacement?->designation,
        ])->values()->all(),
      ];
    })->filter()->values()->all();
  }

  /**
   * Articles actifs, rattachés à l'un des types donnés, dont la date de
   * péremption est atteinte.
   *
   * @param \Illuminate\Support\Collection<int, int> $typeIds
   */
  /**
   * Valeurs saisies à la place d'un numéro absent (en plus de la chaîne vide).
   */
  private const NUMEROS_ABSENTS = ['', '0', '-'];

  /**
   * Articles actifs d'un type numéroté dont le numéro n'est pas renseigné
   * (vide, « 0 » ou « - »), groupés par type. Ne renvoie que les types ayant au moins un tel article.
   * Chaque article est complet (pour être ouvert dans la modale de modification),
   * avec en plus `localisation` : le sapeur à qui il est attribué, sinon son emplacement.
   *
   * @return array<int, array{materiel_type_id: int, designation: string, articles: array<int, array>}>
   */
  public static function getArticlesSansNumero(): array
  {
    $articlesParType = Article::whereIn('materiel_type_id', MaterielType::where('est_numerote', true)->select('id'))
      ->where('statut', true)
      ->whereIn(DB::raw('TRIM(numero)'), self::NUMEROS_ABSENTS)
      ->with(['materielType', 'sapeur', 'emplacement', 'emplacementRepresentee'])
      ->get()
      ->groupBy('materiel_type_id');

    return $articlesParType->map(fn ($articles, int $typeId): array => [
      'materiel_type_id' => $typeId,
      'designation'      => $articles->first()->materielType->designation,
      'articles'         => $articles->map(fn (Article $article): array => [
        ...Arr::except($article->toArray(), ['materiel_type', 'sapeur', 'emplacement']),
        'localisation' => $article->sapeur
          ? "{$article->sapeur->nom} {$article->sapeur->prenom}"
          : $article->emplacement?->designation,
      ])->values()->all(),
    ])->sortBy('designation')->values()->all();
  }

  private static function articlesPerimesQuery($typeIds): Builder
  {
    return Article::whereIn('materiel_type_id', $typeIds)
      ->where('statut', true)
      ->whereNotNull('date_peremption')
      ->whereDate('date_peremption', '<=', Carbon::now()->toDateString());
  }
}
