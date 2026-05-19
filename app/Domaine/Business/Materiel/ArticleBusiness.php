<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Exceptions\InternalException;
use App\Exceptions\BadRequestException;
use App\Models\Article;
use App\Models\BatterieType;
use App\Models\Emplacement;
use App\Models\MaterielType;
use App\Models\Vehicule;
use Illuminate\Database\Eloquent\Collection;
use Nette\Utils\Arrays;

/**
 * Model for manipulating 'item' database table
 * Available public methods
 */
class ArticleBusiness
{
  // const EVENT_STATUS_NONE = "NONE";
  // const EVENT_STATUS_SUCCESS = "SUCCESS";
  // const EVENT_STATUS_WARNING = "WARNING";
  // const EVENT_STATUS_FAILURE = "FAILURE";
  // const EVENT_STATUS_INFO = "INFO";

  // const EVENT_TYPE_LIFECYCLE = "LIFECYCLE";
  // const EVENT_TYPE_INVENTORY = "INVENTORY";
  // const EVENT_TYPE_MAINTENANCE = "MAINTENANCE";

  /**
   * Retour de matériel qui était attribué à un sapeur
   * @param mixed $date
   * @param int[] $articleIds
   * @param int $emplacementId
   * @return void
   */
  public static function retourArticles(int $emplacementId, string $date, array $articleIds): Collection
  {
    Article::whereIn('id', $articleIds)->update(['sapeur_id' => null, 'emplacement_id' => $emplacementId, 'retour' => $date]);
    return Article::whereIn('id', $articleIds)->get();
  }


  /**
   * Attribution d'articles'
   * @param int $sapeurId
   * @param string $date
   * @param int[] $articleIds
   * @return void
   */
  public static function attribuerArticles(int $sapeurId, string $date, array $articleIds): Collection
  {
    // Controle que les articles sont attribuable
    $nonAttribuable = Article::whereIn('articles.id', $articleIds)
      ->join('materiel_types', 'materiel_types.id', '=', 'articles.materiel_type_id')
      ->where('materiel_types.est_attribuable', false)
      ->select(['articles.id', 'articles.materiel_type_id'])
      ->get();

    if (!$nonAttribuable->isEmpty()) {
      throw new ArrayException([$nonAttribuable], message: 'Certains articles ne sont pas attribuable');
    }

    Article::whereIn('id', $articleIds)->update([
      'sapeur_id' => $sapeurId,
      'emplacement_id' => null,
      'attribution' => $date
    ]);
    return Article::whereIn('id', $articleIds)->get();
  }

  public static function creerArticles(array $articles)
  {
    //TODO: Fail safe
    // fetch types equivalents
    $indexedTypes = MaterielType::all()->keyBy('id');

    // Controller qu'un article appartiennent soit à un sapeur soit à un emplacement
    foreach ($articles as $article) {
      $article['sapeur_id'] ??= null;
      $article['emplacement_id'] ??= null;

      if (
        ($article['sapeur_id'] === null && $article['emplacement_id'] === null) ||
        ($article['sapeur_id'] !== null && $article['emplacement_id'] !== null)
      ) {
        throw new ArrayException([], message: 'Certains articles sont à la fois assignés à un sapeur et à un emplacement');
      }

      $type = $indexedTypes[$article['materiel_type_id']];
      if (!$type->est_attribuable && $article['sapeur_id'] !== null) {
        throw new ArrayException([], message: "Article de type '{$type->designation}' n'est pas attribuable");
      }
    }

    // Controller numérotation correcte
    $articles = collect($articles)->map(function ($article) use ($indexedTypes) {
      $type = $indexedTypes[$article['materiel_type_id']];
      return [
        'quantite' => $type->est_numerote ? 1 : $article['quantite'],
        'materiel_type_id' => $article['materiel_type_id'],
        'taille' => $type->est_taillee ? trim($article['taille'] ?? '') : '',
        'remarque' => $article['remarque'] ?? '',
        'emplacement_id' => $article['emplacement_id'],
        'sapeur_id' => $article['sapeur_id'] ?? null,
        'attribution' => ($article['sapeur_id'] ?? null) === null ? null : $article['attribution'],
        'retour' => null,
        'numero' => $type->est_numerote ? $article['numero'] : '',
        // 'uuid' => uniqid(), // TODO: en avons-nous vraiment besoin ? Utiliser le numéro ou le UUID pour les codebar ?
        'achat' => $article['achat'] ?? '',
        'compartiment' => $article['compartiment'] ?? '',
        'est_etiquete' => $article['est_etiquete'] ?? false,
        'est_unique' => false, // TODO: Non utilisé pour le moment
        'chassis' => $article['chassis'] ?? '',
        'designation' => $article['designation'] ?? '',
        'immatriculation' => $article['immatriculation'] ?? '',
      ];
    })->flatMap(fn($article) => array_fill(0, $article['quantite'], $article))
      ->map(fn($article) => [...$article, 'uuid' => uniqid()])
      ->map(fn($data) => Article::create($data))
      ->all();
  }

  public static function editArticles(array $articles): array
  {
    // fetch types equivalents
    $indexedTypes = MaterielType::all()->keyBy('id');

    // Controller qu'un article appartiennent soit à un sapeur soit à un emplacement
    $articles = collect($articles)->map(function ($article) use ($indexedTypes) {
      if (
        ($article['sapeur_id'] === null && $article['emplacement_id'] === null) ||
        ($article['sapeur_id'] !== null && $article['emplacement_id'] !== null)
      ) {
        throw new ArrayException([], message: 'Certains articles sont à la fois assignés à un sapeur et à un emplacement');
      }

      $existant = Article::find($article['id']);
      $article['materiel_type_id'] = $existant->materiel_type_id;
      $type = $indexedTypes[$existant->materiel_type_id];
      if (!$type->est_attribuable && $article['sapeur_id'] !== null) {
        throw new ArrayException([], message: "Article de type '{$type->designation}' n'est pas attribuable");
      }
      return $article;
    })->all();

    // Controller numérotation correcte
    $articles = collect($articles)->map(function ($article) use ($indexedTypes) {
      $type = $indexedTypes[$article['materiel_type_id']];
      return [
        'id' => $article['id'],
        'taille' => $type->est_taillee ? trim($article['taille'] ?? '') : '',
        'remarque' => $article['remarque'] ?? '',
        'emplacement_id' => $article['emplacement_id'],
        'sapeur_id' => $article['sapeur_id'],
        'attribution' => $article['sapeur_id'] === null ? null : $article['attribution'],
        'retour' => null,
        'numero' => $type->est_numerote ? $article['numero'] : '',
        'achat' => $article['achat'] ?? '',
        'compartiment' => $article['compartiment'] ?? '',
        'est_etiquete' => $article['est_etiquete'] ?? false,
        'est_unique' => false, // TODO: Non utilisé pour le moment
        'chassis' => $article['chassis'] ?? '',
        'designation' => $article['designation'] ?? '',
        'immatriculation' => $article['immatriculation'] ?? '',
        'statut' => $article['statut'] ?? true,
      ];
    })->map(fn($article) => Article::find($article['id'])->update($article))
      ->all();
  }

  public static function deleteArticles(array $articleIds): bool
  {
    return Article::whereIn('id', $articleIds)->delete();
  }

  /**
   * Get list of items of a given sapeur
   * @param integer $sapeurId ID of the sapeur for which to get items
   * @return Collection of #item_existing_details
   */
  public static function getArticlesPourSapeur($sapeurId)
  {
    return Article::where('sapeur_id', $sapeurId)->get();
  }

  /**
   * Get list of items that can be attributed
   * @return Collection of #item_existing_details
   */
  public static function getArticlesAttribuable()
  {
    return Article::whereNull('sapeur_id')
      ->leftJoin('materiel_types', 'articles.materiel_type_id', '=', 'materiel_types.id')
      ->where('materiel_types.est_attribuable', true)
      ->get(['articles.*']);
  }

  /**
   * Get list of items that can be attributed
   * @return Collection of #item_existing_details
   */
  public static function getArticlesLavable()
  {
    return Article::leftJoin('materiel_types', 'articles.materiel_type_id', '=', 'materiel_types.id')
      ->where('materiel_types.est_lavable', true)
      ->get(['articles.*']);
  }

  /**
   * Get list of all items
   * @return Collection of #item_existing_details
   */
  public static function getAllArticles()
  {
    return Article::all();
  }

  /**
   * Get list of items of a given product
   * @param integer $productId ID of the product for which to get items
   * @return Collection of #item_existing_details
   */
  public static function getArticlesParMaterielType($materielTypeId)
  {
    return Article::where('materiel_type_id', $materielTypeId)->with(['lavages'])->get();
  }

  /**
   * Get list of items hierarchized by category, product and sublocation
   * @param integer $locationId ID of the main location to consider (incl. children)
   * @return Collection field "categories" of #location_existing_full
   */
  public static function getArticlesParEmplacement($locationId)
  {
    // TODO: Améliorer pour afficher également tout les sous emplacements.
    Emplacement::all();
    // TODO: Index emplacements O(n)

    $emplacementIds = [$locationId];
    // Iterate over emplacements O(n)

    return Article::whereIn('emplacement_id', $emplacementIds)->with(['lavages'])->get();
  }

  // /**
  //  * Edit an existing item
  //  * @param integer $id ID of the item to edit
  //  * @param #item_edit $data Properties of the item to modify
  //  */
  // public static function editItem($id, $data)
  // {
  //   // TODO: a implémenter

  //   // Determine if is uniquely labeled
  //   $isUniqueLabeled = self::isUniqueLabeled(null, $id);

  //   // If product does not use unique number, and number or isuniquelabeled is set, error
  //   if (
  //     !$isUniqueLabeled
  //     &&
  //     (
  //       Arrays::has($data, 'number')
  //       ||
  //       Arrays::has($data, 'isuniquelabeled')
  //     )
  //   ) {
  //     throw new BadRequestException(
  //       BadRequestException::FORBIDDEN_OPERATION,
  //       "Le matériel n'est pas étiquetté individuellement : les champs number et isuniquelabeled ne doivent pas être définis."
  //     );
  //   }

  //   // If product uses unique number and number or isuniquelabeled is not set, or quantity > 1, error
  //   if (
  //     $isUniqueLabeled
  //     &&
  //     (
  //       !Arrays::has($data, 'number')
  //       ||
  //       !Arrays::has($data, 'isuniquelabeled')
  //     )
  //   ) {
  //     throw new BadRequestException(
  //       BadRequestException::FORBIDDEN_OPERATION,
  //       "Le matériel est étiquetté individuellement : les champs number et isuniquelabeled doivent être définis."
  //     );
  //   }

  //   // Proceed with edition
  //   self::edit("item", $id, Arrays::merge(
  //     [
  //       'location_id' => $data['location']['id'],
  //       'islabeled' => $data['islabeled'] ? 1 : 0,
  //       'compartment' => $data['compartment'],
  //       'remark' => $data['remark']
  //     ],
  //     $isUniqueLabeled
  //     ? [
  //       'number' => $data['number'],
  //       'isuniquelabeled' => $data['isuniquelabeled'] ? 1 : 0
  //     ]
  //     : []
  //   ));
  // }

  // /**
  //  * Delete an existing item (virtually)
  //  * @param integer $id ID of the item to delete
  //  * @param integer $id ID of the user who performs deletion
  //  */
  // public static function deleteVirtuallyItem($id, $userId)
  // {
  //   // TODO: à implémenter !

  //   // Make sure it is not already registered as deleted
  //   $items = self::db()->select("SELECT deleted FROM item WHERE id = ?", [$id]);
  //   if (Arrays::size($items) === 0) {
  //     throw new BadRequestException(
  //       BadRequestException::RESOURCE_NOT_FOUND,
  //       "La pièce avec l'ID $id n'existe pas."
  //     );
  //   }
  //   if (Arrays::first($items)->deleted !== null) {
  //     throw new BadRequestException(
  //       BadRequestException::RESOURCE_NOT_FOUND,
  //       "La pièce avec l'ID $id est déjà enregistrée comme supprimée."
  //     );
  //   }

  //   // Proceed with virtual deletion
  //   self::edit("item", $id, [
  //     'deleted' => date('Y-m-d'),
  //     'deleted_user_id' => $userId
  //   ]);
  // }

  /**
   * @internal
   * Determine if the product of an item is uniquely labeled
   * @param integer $productId ID of the product to get
   * @param integer $itemId ID of the item for which to get product
   * @return boolean true if uniquely labeled
   */
  private static function isUniqueLabeled($productId, $itemId)
  {

    // Determine if querying based on product ID or item ID
    $isQueryByProduct = $productId !== null;

    // Build query
    $condition = $isQueryByProduct
      ? "WHERE P.id = ?"
      : "INNER JOIN item I ON P.id = I.product_id WHERE I.id = ?";
    $query = <<<EOF
      SELECT P.prefix
      FROM product P
      $condition
EOF;

    // Execute query
    $products = self::db()->select($query, [$isQueryByProduct ? $productId : $itemId]);

    // If result is > 1, error
    if (Arrays::size($products) !== 1) {
      throw new InternalException(
        InternalException::BAD_IMPLEMENTATION,
        "Impossible de récupérer le champ prefix d'un produit."
      );
    }

    // Return result
    return Arrays::first($products)->prefix !== null;
  }
}
