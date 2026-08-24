<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Exceptions\InternalException;
use App\Exceptions\BadRequestException;
use App\Models\Article;
use App\Models\BatterieType;
use App\Models\Emplacement;
use App\Models\MaterielType;
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
    $estEmplacement = Article::whereIn('articles.id', $articleIds)
      ->join('materiel_types', 'materiel_types.id', '=', 'articles.materiel_type_id')
      ->where('materiel_types.est_emplacement', true)
      ->exists();
    if ($estEmplacement) {
      throw new ArrayException([], "Un véhicule ne peut pas être retourné dans un emplacement de cette manière");
    }

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

  public static function creerArticles(array $articles): array
  {
    //TODO: Fail safe
    // fetch types equivalents
    $indexedTypes = MaterielType::all()->keyBy('id');

    // Controller qu'un article appartiennent soit à un sapeur soit à un emplacement
    foreach ($articles as $article) {
      $article['sapeur_id'] ??= null;
      $article['emplacement_id'] ??= null;
      $type = $indexedTypes[$article['materiel_type_id']];

      // Un article qui est lui-même un emplacement (ex: véhicule) n'est ni attribué
      // à un sapeur, ni rangé dans un emplacement classique : sa position est portée
      // par le parent_id de l'emplacement qu'il représente.
      if (
        !$type->est_emplacement &&
        (
          ($article['sapeur_id'] === null && $article['emplacement_id'] === null) ||
          ($article['sapeur_id'] !== null && $article['emplacement_id'] !== null)
        )
      ) {
        throw new ArrayException([], message: 'Certains articles sont à la fois assignés à un sapeur et à un emplacement');
      }

      if (!$type->est_attribuable && $article['sapeur_id'] !== null) {
        throw new ArrayException([], message: "Article de type '{$type->designation}' n'est pas attribuable");
      }
    }

    // Controller numérotation correcte
    return collect($articles)->map(function ($article) use ($indexedTypes) {
      $type = $indexedTypes[$article['materiel_type_id']];
      return [
        'quantite' => $type->est_numerote ? 1 : $article['quantite'],
        'materiel_type_id' => $article['materiel_type_id'],
        'taille' => $type->est_taillee ? trim($article['taille'] ?? '') : '',
        'remarque' => $article['remarque'] ?? '',
        'emplacement_id' => $type->est_emplacement ? null : $article['emplacement_id'],
        'sapeur_id' => $type->est_emplacement ? null : ($article['sapeur_id'] ?? null),
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
        'emplacement' => $article['emplacement'] ?? null,
      ];
    })->flatMap(fn($article) => array_fill(0, $article['quantite'], $article))
      ->map(fn($article) => [...$article, 'uuid' => uniqid()])
      ->map(function ($data) use ($indexedTypes) {
        $emplacementData = $data['emplacement'];
        unset($data['emplacement']);

        $type = $indexedTypes[$data['materiel_type_id']];
        if ($type->est_emplacement && ($emplacementData === null || ($emplacementData['couleur_id'] ?? null) === null)) {
          throw new ArrayException([], "Une couleur est requise pour un article qui est aussi un emplacement");
        }

        $created = Article::create($data);
        if ($type->est_emplacement) {
          EmplacementBusiness::createEmplacementPourArticle($created, $emplacementData);
        }

        return $created->load('emplacementRepresentee');
      })
      ->all();
  }

  public static function editArticles(array $articles): Collection
  {
    // fetch types equivalents
    $indexedTypes = MaterielType::all()->keyBy('id');

    // Controller qu'un article appartiennent soit à un sapeur soit à un emplacement
    $articles = collect($articles)->map(function ($article) use ($indexedTypes) {
      $article['sapeur_id'] ??= null;
      $article['emplacement_id'] ??= null;

      $existant = Article::find($article['id']);
      $oldType = $indexedTypes[$existant->materiel_type_id];

      // Le type de matériel d'un article est verrouillé, sauf pour un article qui est
      // lui-même un emplacement (ex: véhicule) : il peut alors changer pour un autre
      // sous-type partageant le même discriminant (ex: un autre sous-type de véhicule).
      if (isset($article['materiel_type_id']) && (int) $article['materiel_type_id'] !== $existant->materiel_type_id) {
        if (!$oldType->est_emplacement) {
          throw new ArrayException([], "Le type de matériel de cet article ne peut pas être modifié");
        }
        $newType = $indexedTypes[(int) $article['materiel_type_id']] ?? null;
        if ($newType === null || $newType->type !== $oldType->type) {
          throw new ArrayException([], "Le nouveau type doit être un autre sous-type de véhicule");
        }
        $article['materiel_type_id'] = (int) $article['materiel_type_id'];
      } else {
        $article['materiel_type_id'] = $existant->materiel_type_id;
      }

      $type = $indexedTypes[$article['materiel_type_id']];

      // Un article qui est lui-même un emplacement (ex: véhicule) n'est ni attribué
      // à un sapeur, ni rangé dans un emplacement classique.
      if (
        !$type->est_emplacement &&
        (
          ($article['sapeur_id'] === null && $article['emplacement_id'] === null) ||
          ($article['sapeur_id'] !== null && $article['emplacement_id'] !== null)
        )
      ) {
        throw new ArrayException([], message: 'Certains articles sont à la fois assignés à un sapeur et à un emplacement');
      }

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
        'materiel_type_id' => $article['materiel_type_id'],
        'taille' => $type->est_taillee ? trim($article['taille'] ?? '') : '',
        'remarque' => $article['remarque'] ?? '',
        'emplacement_id' => $type->est_emplacement ? null : $article['emplacement_id'],
        'sapeur_id' => $type->est_emplacement ? null : $article['sapeur_id'],
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
        'emplacement' => $article['emplacement'] ?? null,
      ];
    })->map(function ($article) use ($indexedTypes) {
      $emplacementData = $article['emplacement'];
      unset($article['emplacement']);

      $existing = Article::find($article['id']);
      $type = $indexedTypes[$existing->materiel_type_id];
      if ($type->est_emplacement && ($emplacementData === null || ($emplacementData['couleur_id'] ?? null) === null)) {
        throw new ArrayException([], "Une couleur est requise pour un article qui est aussi un emplacement");
      }
      if ($type->est_emplacement && $existing->emplacementRepresentee === null) {
        throw new ArrayException([], "Cet article n'a pas encore d'emplacement lié, utilisez l'outil de migration pour le créer");
      }

      $existing->update($article);

      if ($type->est_emplacement) {
        $emplacementRepresentee = $existing->emplacementRepresentee;
        $nouveauParentId = $emplacementData['parent_id'] ?? null;
        EmplacementBusiness::assertNoCycle($emplacementRepresentee->id, $nouveauParentId);
        $emplacementRepresentee->update([
          'designation' => $existing->designation,
          'remarque' => $existing->remarque,
          'couleur_id' => $emplacementData['couleur_id'],
          'parent_id' => $nouveauParentId,
          'est_etiquete' => $emplacementData['est_etiquete'] ?? false,
          'est_compartimentable' => $emplacementData['est_compartimentable'] ?? false,
        ]);
      }
      // TODO: synchroniser emplacementRepresentee->statut avec celui de l'article ?
      // Laissé manuel pour le moment.

      return $article['id'];
    });

    return Article::whereIn('id', $articles)->with('emplacementRepresentee')->get();
  }

  public static function deleteArticles(array $articleIds): bool
  {
    $emplacementsLies = Emplacement::whereIn('article_id', $articleIds)->get();
    foreach ($emplacementsLies as $emplacement) {
      if (Article::where('emplacement_id', $emplacement->id)->exists()) {
        throw new ArrayException([], "Impossible de supprimer un véhicule tant que du matériel est rangé dans son emplacement");
      }
      if (Emplacement::where('parent_id', $emplacement->id)->exists()) {
        throw new ArrayException([], "Impossible de supprimer un véhicule tant que son emplacement contient des sous-emplacements");
      }
    }
    Emplacement::whereIn('id', $emplacementsLies->pluck('id'))->delete();
    return Article::whereIn('id', $articleIds)->delete();
  }

  /**
   * Get list of items of a given sapeur
   * @param integer $sapeurId ID of the sapeur for which to get items
   * @return Collection of #item_existing_details
   */
  public static function getArticlesPourSapeur($sapeurId)
  {
    return Article::where('sapeur_id', $sapeurId)->with(['emplacementRepresentee'])->get();
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
      ->with(['emplacementRepresentee'])
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
      ->with(['emplacementRepresentee'])
      ->get(['articles.*']);
  }

  /**
   * Get list of all items
   * @return Collection of #item_existing_details
   */
  public static function getAllArticles()
  {
    return Article::with(['emplacementRepresentee'])->get();
  }

  /**
   * Get list of items of a given product
   * @param integer $materielTypeId ID of the product for which to get items
   * @return Collection of #item_existing_details
   */
  public static function getArticlesParMaterielType($materielTypeId)
  {
    return Article::where('materiel_type_id', $materielTypeId)->with(['lavages', 'emplacementRepresentee'])->get();
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
}
