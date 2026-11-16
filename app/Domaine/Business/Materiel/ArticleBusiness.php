<?php

namespace App\Domaine\Business\Materiel;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Exceptions\ArrayException;
use App\Exceptions\InternalException;
use App\Exceptions\BadRequestException;
use App\Models\Sapeur;
use App\Models\Article;
use App\Models\BatterieType;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\Emplacement;
use App\Models\MaterielType;
use App\Domaine\Business\SisParamBusiness;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Arrays;

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
   * Retour de matériel qui était attribué à un sapeur : clôt les attributions
   * ouvertes et range les articles dans l'emplacement.
   *
   * @param int[] $articleIds
   * @return Collection<int, Article>
   */
  public static function retourArticles(int $emplacementId, string $date, array $articleIds, ?int $auteurSapeurId = null): Collection
  {
    $estEmplacement = Article::whereIn('articles.id', $articleIds)
      ->join('materiel_types', 'materiel_types.id', '=', 'articles.materiel_type_id')
      ->where('materiel_types.est_emplacement', true)
      ->exists();
    if ($estEmplacement) {
      throw new ArrayException([], "Un véhicule ne peut pas être retourné dans un emplacement de cette manière");
    }
    EmplacementBusiness::assertEmplacementActif($emplacementId);
    self::assertAucunSupprime($articleIds, "Certains articles sont supprimés et ne peuvent pas être rangés");

    DB::transaction(function () use ($emplacementId, $date, $articleIds, $auteurSapeurId) {
      foreach (Article::whereIn('id', $articleIds)->get() as $article) {
        AttributionBusiness::cloturer($article, $date, $emplacementId, $auteurSapeurId);
      }
    });

    return Article::whereIn('id', $articleIds)->get();
  }

  /**
   * Attribution d'articles à un sapeur. Un article déjà attribué est transféré :
   * son attribution en cours est close à la date de la nouvelle.
   *
   * @param int[] $articleIds
   * @return Collection<int, Article>
   */
  public static function attribuerArticles(int $sapeurId, string $date, array $articleIds, ?int $auteurSapeurId = null): Collection
  {
    Sapeur::findOrFail($sapeurId);

    // Controle que les articles sont attribuable
    $nonAttribuable = Article::whereIn('articles.id', $articleIds)
      ->join('materiel_types', 'materiel_types.id', '=', 'articles.materiel_type_id')
      ->where('materiel_types.est_attribuable', false)
      ->select(['articles.id', 'articles.materiel_type_id'])
      ->get();

    if (!$nonAttribuable->isEmpty()) {
      throw new ArrayException([$nonAttribuable], message: 'Certains articles ne sont pas attribuable');
    }
    self::assertAucunSupprime($articleIds, "Certains articles sont supprimés et ne peuvent pas être attribués");

    DB::transaction(function () use ($sapeurId, $date, $articleIds, $auteurSapeurId) {
      foreach (Article::whereIn('id', $articleIds)->get() as $article) {
        if ($article->sapeur_id !== null) {
          AttributionBusiness::cloturer($article, $date, null, $auteurSapeurId);
        }
        AttributionBusiness::ouvrir($article, $sapeurId, $date, $auteurSapeurId);
      }
    });

    return Article::whereIn('id', $articleIds)->get();
  }

  /**
   * @param int[] $articleIds
   * @throws ArrayException si l'un des articles est supprimé
   */
  private static function assertAucunSupprime(array $articleIds, string $message): void
  {
    if (Article::whereIn('id', $articleIds)->whereNotNull('supprime_le')->exists()) {
      throw new ArrayException([], $message);
    }
  }

  /**
   * Création d'articles ; un article créé chez un sapeur ouvre une attribution
   * à la date `attribution`.
   *
   * @param array<int, array<string, mixed>> $articles
   * @return array<int, Article>
   */
  public static function creerArticles(array $articles, ?int $auteurSapeurId = null): array
  {
    // fetch types equivalents
    $indexedTypes = MaterielType::all()->keyBy('id');

    // sapeur_id / emplacement_id sont facultatifs dans la requête : absents, ils valent null.
    $articles = array_map(fn ($article) => [
      ...$article,
      'sapeur_id' => $article['sapeur_id'] ?? null,
      'emplacement_id' => $article['emplacement_id'] ?? null,
    ], $articles);

    // Controller qu'un article appartiennent soit à un sapeur soit à un emplacement
    foreach ($articles as $article) {
      $type = $indexedTypes[$article['materiel_type_id']];

      if (!$type->statut) {
        throw new ArrayException([], message: "Le type '{$type->designation}' est inactif");
      }

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

      if ($type->est_perimable && ($article['date_peremption'] ?? null) === null) {
        throw new ArrayException([], message: "Article de type '{$type->designation}' nécessite une date de péremption");
      }

      if ($type->est_numerote && trim($article['numero'] ?? '') === '') {
        throw new ArrayException([], message: "Article de type '{$type->designation}' nécessite un numéro");
      }

      if (!$type->est_emplacement && $article['emplacement_id'] !== null) {
        EmplacementBusiness::assertEmplacementActif((int) $article['emplacement_id']);
      }
      if ($type->est_emplacement) {
        $parentId = $article['emplacement']['parent_id'] ?? null;
        EmplacementBusiness::assertParentActif($parentId !== null ? (int) $parentId : null, true);
      }
    }

    // Controller numérotation correcte
    // Transaction : si un article du lot est invalide (ex: couleur manquante pour un
    // emplacement), aucun des articles déjà créés dans ce même lot ne doit rester en base.
    return DB::transaction(fn() => collect($articles)->map(function ($article) use ($indexedTypes, $auteurSapeurId) {
      $type = $indexedTypes[$article['materiel_type_id']];
      $sapeurId = $type->est_emplacement ? null : $article['sapeur_id'];
      return [
        'quantite' => $type->est_numerote ? 1 : ($article['quantite'] ?? 1),
        'materiel_type_id' => $article['materiel_type_id'],
        'taille' => $type->est_taillee ? trim($article['taille'] ?? '') : '',
        'remarque' => $article['remarque'] ?? '',
        'emplacement_id' => $type->est_emplacement ? null : $article['emplacement_id'],
        'sapeur_id' => null,
        'ajoute_par_sapeur_id' => $auteurSapeurId,
        'attribution_sapeur_id' => $sapeurId,
        'attribution' => $sapeurId === null ? null : $article['attribution'],
        'numero' => $type->est_numerote ? $article['numero'] : '',
        // 'uuid' => uniqid(), // TODO: en avons-nous vraiment besoin ? Utiliser le numéro ou le UUID pour les codebar ?
        'achat' => $article['achat'] ?? '',
        'compartiment' => $article['compartiment'] ?? '',
        'est_etiquete' => $article['est_etiquete'] ?? false,
        'est_unique' => false, // TODO: Non utilisé pour le moment
        'chassis' => $article['chassis'] ?? '',
        'designation' => $article['designation'] ?? '',
        'immatriculation' => $article['immatriculation'] ?? '',
        'date_peremption' => $type->est_perimable ? ($article['date_peremption'] ?? null) : null,
        'disponible_intervention' => $article['disponible_intervention'] ?? true,
        'emplacement' => $article['emplacement'] ?? null,
      ];
    })->flatMap(fn($article) => array_fill(0, $article['quantite'], $article))
      ->map(fn($article) => [...$article, 'uuid' => uniqid()])
      ->map(function ($data) use ($indexedTypes, $auteurSapeurId) {
        $emplacementData = $data['emplacement'];
        $attributionSapeurId = $data['attribution_sapeur_id'];
        $attributionDate = $data['attribution'];
        unset($data['emplacement'], $data['attribution_sapeur_id'], $data['attribution']);

        $type = $indexedTypes[$data['materiel_type_id']];
        if ($type->est_emplacement && ($emplacementData === null || ($emplacementData['couleur_id'] ?? null) === null)) {
          throw new ArrayException([], "Une couleur est requise pour un article qui est aussi un emplacement");
        }

        $created = Article::create($data);
        if ($type->est_emplacement) {
          EmplacementBusiness::createEmplacementPourArticle($created, $emplacementData);
        }
        if ($attributionSapeurId !== null) {
          AttributionBusiness::ouvrir($created, (int) $attributionSapeurId, $attributionDate, $auteurSapeurId);
        }

        return $created->load('emplacementRepresentee');
      })
      ->all());
  }

  /**
   * Modification d'articles. Un article supprimé ne peut plus être modifié
   * (ni déplacé) : il doit d'abord être restauré.
   *
   * @param array<int, array<string, mixed>> $articles
   * @return Collection<int, Article>
   * @throws ArrayException si un article est supprimé ou si une donnée est invalide
   */
  public static function editArticles(array $articles): Collection
  {
    self::assertAucunSupprime(
      array_column($articles, 'id'),
      "Certains articles sont supprimés et ne peuvent pas être modifiés, restaurez-les d'abord"
    );

    // fetch types equivalents
    $indexedTypes = MaterielType::all()->keyBy('id');

    // Controller qu'un article appartiennent soit à un sapeur soit à un emplacement
    $articles = collect($articles)->map(function ($article) use ($indexedTypes) {
      $existant = Article::find($article['id']);

      // Le détenteur ne change que par attribuer / rendre / supprimer : le sapeur
      // reçu est ignoré, et un article attribué n'est rangé dans aucun emplacement.
      $article['sapeur_id'] = $existant->sapeur_id;
      $article['emplacement_id'] = $existant->sapeur_id === null ? ($article['emplacement_id'] ?? null) : null;
      unset($article['attribution']);
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

      if ($type->est_perimable && ($article['date_peremption'] ?? null) === null) {
        throw new ArrayException([], message: "Article de type '{$type->designation}' nécessite une date de péremption");
      }

      if ($type->est_numerote && trim($article['numero'] ?? '') === '') {
        throw new ArrayException([], message: "Article de type '{$type->designation}' nécessite un numéro");
      }

      // Seul un déplacement est contrôlé : un article déjà rangé dans un
      // emplacement inactif (données historiques) reste modifiable.
      if (
        !$type->est_emplacement &&
        $article['emplacement_id'] !== null &&
        (int) $article['emplacement_id'] !== $existant->emplacement_id
      ) {
        EmplacementBusiness::assertEmplacementActif((int) $article['emplacement_id']);
      }
      return $article;
    })->all();

    // Controller numérotation correcte
    // Transaction : un refus sur l'emplacement lié d'un article (cycle, parent
    // inactif…) ne doit laisser aucun article du lot à moitié modifié.
    $articles = DB::transaction(fn () => collect($articles)->map(function ($article) use ($indexedTypes) {
      $type = $indexedTypes[$article['materiel_type_id']];
      return [
        'id' => $article['id'],
        'materiel_type_id' => $article['materiel_type_id'],
        'taille' => $type->est_taillee ? trim($article['taille'] ?? '') : '',
        'remarque' => $article['remarque'] ?? '',
        'emplacement_id' => $type->est_emplacement ? null : $article['emplacement_id'],
        'numero' => $type->est_numerote ? $article['numero'] : '',
        'achat' => $article['achat'] ?? '',
        'compartiment' => $article['compartiment'] ?? '',
        'est_etiquete' => $article['est_etiquete'] ?? false,
        'est_unique' => false, // TODO: Non utilisé pour le moment
        'chassis' => $article['chassis'] ?? '',
        'designation' => $article['designation'] ?? '',
        'immatriculation' => $article['immatriculation'] ?? '',
        'date_peremption' => $type->est_perimable ? ($article['date_peremption'] ?? null) : null,
        'emplacement' => $article['emplacement'] ?? null,
        // Absent de la requête : la disponibilité actuelle est conservée.
        ...(array_key_exists('disponible_intervention', $article) ? ['disponible_intervention' => (bool) $article['disponible_intervention']] : []),
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

        EmplacementBusiness::assertParentActif(
          $nouveauParentId !== null ? (int) $nouveauParentId : null,
          (bool) $emplacementRepresentee->statut,
          $emplacementRepresentee,
        );

        $emplacementRepresentee->update([
          'designation' => $existing->designation,
          'remarque' => $existing->remarque,
          'couleur_id' => $emplacementData['couleur_id'],
          'parent_id' => $nouveauParentId,
          'est_etiquete' => $emplacementData['est_etiquete'] ?? false,
          'est_compartimentable' => $emplacementData['est_compartimentable'] ?? false,
        ]);
      }

      return $article['id'];
    }));

    return Article::whereIn('id', $articles)->with('emplacementRepresentee')->get();
  }

  /**
   * Suppression logique d'articles : ils restent en base avec la date, l'auteur et
   * la remarque de suppression, et gardent leur dernier emplacement connu. Une
   * attribution ouverte est close à la date de suppression. Supprimer un véhicule
   * désactive son emplacement, qui ne doit plus contenir de matériel non supprimé
   * ni de sous-emplacement actif.
   *
   * @param int[] $articleIds
   * @return Collection<int, Article>
   * @throws ArrayException si la date est future, si un article est déjà supprimé
   *   ou si l'emplacement d'un véhicule ne peut pas être désactivé
   */
  public static function supprimerArticles(array $articleIds, string $date, ?string $remarque, ?int $auteurSapeurId): Collection
  {
    self::assertDatePasseeOuAujourdhui($date);
    self::assertAucunSupprime($articleIds, "Certains articles sont déjà supprimés");

    return DB::transaction(function () use ($articleIds, $date, $remarque, $auteurSapeurId) {
      $articles = Article::whereIn('id', $articleIds)->with('emplacementRepresentee')->get();
      foreach ($articles as $article) {
        if ($article->sapeur_id !== null) {
          AttributionBusiness::cloturer($article, $date, null, $auteurSapeurId);
        }

        $vehicleLocation = $article->emplacementRepresentee;
        if ($vehicleLocation !== null) {
          EmplacementBusiness::assertCanBeDeactivated($vehicleLocation->id);
          $vehicleLocation->update(['statut' => false]);
        }

        $article->update([
          'supprime_le' => $date,
          'supprime_par_sapeur_id' => $auteurSapeurId,
          'remarque_suppression' => $remarque,
        ]);
      }

      return $articles->each->refresh();
    });
  }

  /**
   * Suppression logique d'une quantité de pièces d'un lot non numéroté (même type,
   * même emplacement, même compartiment). Le serveur choisit les pièces : péremption
   * la plus proche d'abord (sans date en dernier), puis l'ajout le plus ancien.
   *
   * @return Collection<int, Article>
   * @throws ArrayException si le type est numéroté ou si le lot contient moins de
   *   pièces que la quantité demandée
   */
  public static function supprimerLot(int $materielTypeId, int $emplacementId, string $compartiment, int $quantite, string $date, ?string $remarque, ?int $auteurSapeurId): Collection
  {
    // Les articles numérotés sont des pièces uniques : on les supprime un par un.
    if (MaterielType::findOrFail($materielTypeId)->est_numerote) {
      throw new ArrayException([], "Un article numéroté ne peut pas être supprimé par lot, supprimez-le individuellement");
    }

    return DB::transaction(function () use ($materielTypeId, $emplacementId, $compartiment, $quantite, $date, $remarque, $auteurSapeurId) {
      $pieceIds = Article::nonSupprimes()
        ->where('materiel_type_id', $materielTypeId)
        ->where('emplacement_id', $emplacementId)
        ->where('compartiment', $compartiment)
        ->orderByRaw('date_peremption IS NULL')
        ->orderBy('date_peremption')
        ->orderBy('created_at')
        ->orderBy('id')
        ->limit($quantite)
        ->lockForUpdate()
        ->pluck('id')
        ->all();

      if (count($pieceIds) < $quantite) {
        $available = count($pieceIds);
        throw new ArrayException([], "Le lot ne contient que {$available} pièce(s), impossible d'en supprimer {$quantite}");
      }

      return self::supprimerArticles($pieceIds, $date, $remarque, $auteurSapeurId);
    });
  }

  /**
   * Restauration d'articles supprimés : les champs de suppression repassent à NULL.
   * Un article est rangé dans l'emplacement donné, obligatoire et actif ; un véhicule
   * n'est pas rangé (l'emplacement est ignoré) mais son emplacement est réactivé
   * (le parent doit être actif). Une attribution n'est jamais rouverte.
   *
   * @param int[] $articleIds
   * @return Collection<int, Article>
   * @throws ArrayException si un article n'est pas supprimé, si l'emplacement manque
   *   pour un article qui n'est pas un véhicule, ou si l'emplacement (ou le parent
   *   de l'emplacement d'un véhicule) est inactif
   */
  public static function restaurerArticles(array $articleIds, ?int $emplacementId): Collection
  {
    if (Article::whereIn('id', $articleIds)->nonSupprimes()->exists()) {
      throw new ArrayException([], "Certains articles ne sont pas supprimés");
    }

    return DB::transaction(function () use ($articleIds, $emplacementId) {
      $articles = Article::whereIn('id', $articleIds)->with('emplacementRepresentee')->get();
      foreach ($articles as $article) {
        $articleData = [
          'supprime_le' => null,
          'supprime_par_sapeur_id' => null,
          'remarque_suppression' => null,
        ];

        $vehicleLocation = $article->emplacementRepresentee;
        if ($vehicleLocation !== null) {
          EmplacementBusiness::assertParentActif($vehicleLocation->parent_id, true);
          $vehicleLocation->update(['statut' => true]);
        } else {
          if ($emplacementId === null) {
            throw new ArrayException([], "Un emplacement est requis pour restaurer un article qui n'est pas un véhicule");
          }
          EmplacementBusiness::assertEmplacementActif($emplacementId);
          $articleData['emplacement_id'] = $emplacementId;
        }

        $article->update($articleData);
      }

      return $articles->each->refresh();
    });
  }

  /**
   * @throws ArrayException si la date est postérieure à aujourd'hui
   */
  private static function assertDatePasseeOuAujourdhui(string $date): void
  {
    if (Carbon::parse($date)->startOfDay()->gt(Carbon::today())) {
      throw new ArrayException([], "La date ne peut pas être dans le futur");
    }
  }

  /**
   * Pose sur chaque article l'attribut `attribution` : date de début de son
   * attribution ouverte (lue par les gabarits PDF). Suppose `attributionOuverte` chargée.
   *
   * @param Collection<int, Article> $articles
   * @return Collection<int, Article>
   */
  public static function avecDateAttribution(Collection $articles): Collection
  {
    foreach ($articles as $article) {
      $article->setAttribute('attribution', $article->attributionOuverte?->debut);
    }

    return $articles;
  }

  /**
   * Articles détenus par un sapeur, avec la date de début de leur attribution
   * en cours (attribut `attribution`).
   *
   * @param integer $sapeurId ID of the sapeur for which to get articles
   * @return Collection<int, Article>
   */
  public static function getArticlesPourSapeur($sapeurId): Collection
  {
    return self::avecDateAttribution(
      Article::where('sapeur_id', $sapeurId)->with(['emplacementRepresentee', 'attributionOuverte'])->get()
    );
  }

  /**
   * Get list of articles that can be attributed
   * @return Collection<int, Article>
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
   * Get list of all articles
   * @return Collection<int, Article>
   */
  public static function getAllArticles()
  {
    return Article::with(['emplacementRepresentee'])->get();
  }

  /**
   * Get list of articles of a given materiel type
   * @param integer $materielTypeId ID of the materiel type for which to get articles
   * @return Collection<int, Article>
   */
  public static function getArticlesParMaterielType($materielTypeId)
  {
    $articles = Article::where('materiel_type_id', $materielTypeId)->with(['emplacementRepresentee'])->get();

    return self::avecUsages($articles);
  }

  /**
   * Pose sur chaque article l'attribut `usages` : nombre d'exécutions par
   * contrôle à usage limité (nb_execution_max non null), indexé par id de
   * contrôle. Sérialisé en objet JSON (`{}` si vide).
   *
   * @param Collection<int, Article> $articles
   * @return Collection<int, Article>
   */
  public static function avecUsages(Collection $articles): Collection
  {
    $lignes = ControleExec::whereIn('article_id', $articles->modelKeys())
      ->whereIn('controle_id', Controle::whereNotNull('nb_execution_max')->select('id'))
      ->groupBy('article_id', 'controle_id')
      ->selectRaw('article_id, controle_id, count(*) as nb')
      ->get()
      ->groupBy('article_id');

    foreach ($articles as $article) {
      $usages = [];
      foreach ($lignes->get($article->id, []) as $ligne) {
        $usages[$ligne->controle_id] = (int) $ligne->nb;
      }
      $article->setAttribute('usages', (object) $usages);
    }

    return $articles;
  }

  /**
   * Articles actifs encore attribués à des sapeurs inactifs (matériel à récupérer).
   * Le regroupement par sapeur est fait côté frontend.
   *
   * @return Collection<int, Article>
   */
  public static function getArticlesARecuperer()
  {
    return Article::nonSupprimes()
      ->whereNotNull('sapeur_id')
      ->whereHas('sapeur', fn ($q) => $q->where('actif', false))
      ->with(['sapeur', 'materielType'])
      ->get();
  }

  /**
   * Get list of articles rangés dans un emplacement et ses descendants
   * @param integer $locationId ID de l'emplacement racine à considérer (avec ses descendants)
   * @return Collection<int, Article>
   */
  public static function getArticlesParEmplacement($locationId)
  {
    $emplacementIds = EmplacementBusiness::descendantIds((int) $locationId);

    // Les sous-emplacements qui représentent eux-mêmes un véhicule (article
    // possédant un emplacement) doivent apparaître dans la liste, en plus du
    // matériel directement rangé. On exclut $locationId lui-même : si on
    // consulte l'emplacement d'un véhicule, ce véhicule ne doit pas
    // apparaître comme un article rangé dans son propre emplacement.
    $vehiculeArticleIds = Emplacement::whereIn('id', array_diff($emplacementIds, [(int) $locationId]))
      ->whereNotNull('article_id')
      ->pluck('article_id');

    $articles = Article::where(function ($query) use ($emplacementIds, $vehiculeArticleIds) {
      $query->whereIn('emplacement_id', $emplacementIds)
        ->orWhereIn('id', $vehiculeArticleIds);
    })->get();

    return self::avecUsages($articles);
  }

  public static function pdfArticlePourSapeur(int $sapeurId, $sisKey)
  {
    $sapeur = Sapeur::findOrFail($sapeurId);

    $articles = self::avecDateAttribution(
      Article::where('sapeur_id', '=', $sapeurId)->with(['materielType', 'attributionOuverte'])->get()
    )
      ->sortBy('materielType.designation')
      ->values();
    $logoPath = SisParamBusiness::getLogo($sisKey);
    $content = TypstToPdfGenerator::generateDocument(
        TypstTemplate::ArticlesPourSapeur,
        [
            "sapeur" => $sapeur,
            "articles" => $articles,
        ],
        $logoPath
    );
    return response()->streamDownload(
        function () use ($content) {
            echo $content;
        },
        'liste-articles.pdf'
    );
  }
}
