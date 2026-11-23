<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use App\Models\MaterielType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Traitement des écarts d'un inventaire validé par le responsable matériel.
 *
 * Un écart n'est pas un delta appliqué à la photo du démarrage : il compare ce que le
 * sapeur a constaté au stock d'aujourd'hui, et propose d'aligner le stock sur le constaté.
 * Le stock a pu changer depuis l'inventaire (ajouts, attributions, déplacements,
 * suppressions) : l'écart est recalculé à chaque affichage et à chaque application.
 *
 * - Lot non numéroté : un écart de quantité (manquantes ou en plus) et un écart des
 *   pièces défectueuses. La quantité visée est le constaté, moins les défectueuses déjà
 *   retirées du stock par ce traitement. Le stock du lot, ce sont ses pièces encore dans
 *   l'emplacement (même modifiées depuis : compartiment, taille…) et les pièces de même
 *   clé ajoutées depuis : celles-ci comblent un manque mais ne se retirent jamais, le
 *   comptage ne les a pas vues.
 * - Pièce numérotée : manquante, défectueuse ou trouvée en plus, selon où est l'article
 *   aujourd'hui.
 *
 * Un écart sans action à faire est « déjà à jour » (le stock correspond au constaté) ou
 * « plus concerné » (l'article a bougé depuis) : il se clôt sans toucher au stock. Une
 * décision appliquée est définitive.
 */
class InventaireEcartBusiness
{
    public const DECISIONS = ['SORTIR_DU_STOCK', 'AJOUTER_AU_STOCK', 'AUCUNE_ACTION'];

    /** Remarque de traitement par défaut d'un écart clos sans action. */
    private const LIBELLES_RESOLUTION = [
        'A_JOUR' => 'Déjà à jour',
        'PLUS_CONCERNE' => "Plus concerné : l'article a bougé depuis l'inventaire",
    ];

    /**
     * Écarts d'un inventaire validé, comparés au stock actuel : pour chacun, ce qui était
     * prévu, ce qui est en stock aujourd'hui, ce qui a été constaté, l'action proposée et la
     * signature de cet état (à renvoyer pour appliquer une décision).
     *
     * @return list<array<string, mixed>>
     */
    public static function ecarts(Inventaire $inventaire): array
    {
        $inventaire->load(['emplacements.emplacement', 'lignes.article']);
        $noeuds = $inventaire->emplacements->keyBy('id');
        $lignes = $inventaire->lignes->sortBy('id');
        [$connus, $nouveaux] = self::stock($noeuds, $lignes);

        $ecarts = [];
        foreach ($lignes->whereNull('lot') as $ligne) {
            $ecart = self::ecartPiece($ligne, $noeuds[$ligne->inventaire_emplacement_id]);
            if ($ecart !== null) {
                $ecarts[] = $ecart;
            }
        }
        foreach ($lignes->whereNotNull('lot')->groupBy('lot') as $cle => $lot) {
            array_push($ecarts, ...self::ecartsLot($lot->values(), $noeuds[$lot->first()->inventaire_emplacement_id], $connus, $nouveaux[$cle] ?? collect()));
        }

        return $ecarts;
    }

    /**
     * Applique une décision à l'écart `$ecartId`, recalculé contre le stock actuel :
     *
     * - AUCUNE_ACTION : l'écart est clos, le stock n'est pas touché (seule décision possible
     *   pour un écart déjà à jour ou plus concerné).
     * - SORTIR_DU_STOCK : les pièces en trop (ou défectueuses, ou l'article manquant) sont
     *   supprimées à la date du jour (suppression logique, restaurable).
     * - AJOUTER_AU_STOCK : l'article trouvé est rangé ici, ou les pièces manquantes au stock
     *   sont créées, avec `$materielTypeId` pour choisir ou corriger le type, `$article` pour
     *   la configuration commune et `$articles` pièce par pièce (dans l'ordre des pièces).
     *
     * `$signature` est celle de l'écart affiché : si le stock a changé depuis, rien n'est
     * appliqué.
     *
     * @param array<string, mixed> $article
     * @param list<array<string, mixed>> $articles
     * @throws ArrayException si l'inventaire n'est pas validé, si l'écart est inconnu ou déjà
     *                        traité, si le stock a changé depuis l'affichage ou si la
     *                        décision ne convient pas à l'écart
     */
    public static function traiter(
        int $inventaireId,
        string $ecartId,
        string $signature,
        string $decision,
        ?string $remarque,
        ?int $materielTypeId,
        array $article,
        array $articles,
        ?int $auteurSapeurId,
    ): void {
        DB::transaction(function () use ($inventaireId, $ecartId, $signature, $decision, $remarque, $materielTypeId, $article, $articles, $auteurSapeurId) {
            $inventaire = Inventaire::lockForUpdate()->findOrFail($inventaireId);
            if ($inventaire->statut !== 'VALIDE') {
                throw new ArrayException([], "L'inventaire « {$inventaire->designation} » n'est pas encore validé");
            }

            $ecart = collect(self::ecarts($inventaire))->firstWhere('id', $ecartId);
            if ($ecart === null) {
                throw new ArrayException([], "Cet écart ne fait pas partie de l'inventaire");
            }
            if ($ecart['decision'] !== null) {
                throw new ArrayException([], 'Cet écart a déjà été traité');
            }
            if ($ecart['signature'] !== $signature) {
                throw new ArrayException([], "Le stock a changé depuis l'affichage : vérifiez la proposition mise à jour avant de l'appliquer");
            }
            if (!in_array($decision, $ecart['decisions'], true)) {
                throw new ArrayException([], "Cette décision ne convient pas à l'écart");
            }

            $remarque = $remarque === null || trim($remarque) === '' ? null : trim($remarque);
            if ($decision === 'SORTIR_DU_STOCK') {
                ArticleBusiness::supprimerArticles(
                    $ecart['article_ids_a_retirer'],
                    Carbon::today()->toDateString(),
                    $remarque ?? "Inventaire « {$inventaire->designation} »",
                    $auteurSapeurId,
                );
            }
            if ($decision === 'AJOUTER_AU_STOCK') {
                self::ajouterAuStock($ecart, $materielTypeId, $article, $articles, $auteurSapeurId);
            }

            InventaireLigne::whereIn('id', $ecart['ligne_ids'])->update([
                'decision' => $decision,
                'traite_par_sapeur_id' => $auteurSapeurId,
                'traite_le' => Carbon::now(),
                'remarque_traitement' => $remarque ?? self::LIBELLES_RESOLUTION[$ecart['resolution']] ?? null,
            ]);
        });
    }

    /**
     * Articles non numérotés en stock aujourd'hui dans les emplacements de l'inventaire, les
     * plus anciens d'abord : ceux que référence une ligne de l'inventaire, indexés par id, et
     * ceux ajoutés depuis, indexés par clé de lot.
     *
     * @param Collection<int, InventaireEmplacement> $noeuds
     * @param Collection<int, InventaireLigne> $lignes
     * @return array{0: Collection<int, Article>, 1: Collection<string, Collection<int, Article>>}
     */
    private static function stock(Collection $noeuds, Collection $lignes): array
    {
        $noeudIdParEmplacementId = $noeuds->whereNotNull('emplacement_id')->pluck('id', 'emplacement_id');
        $references = $lignes->pluck('article_id')->filter()->flip();

        [$connus, $nouveaux] = Article::nonSupprimes()
            ->whereIn('emplacement_id', $noeudIdParEmplacementId->keys())
            ->where(fn ($query) => $query->whereNull('numero')->orWhere('numero', ''))
            ->with('materielType')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->partition(fn (Article $article) => $references->has($article->id));

        return [
            $connus->keyBy('id'),
            $nouveaux->groupBy(fn (Article $article) => (new InventaireLigne(
                InventaireBusiness::ligneAttendue($article, $noeudIdParEmplacementId[$article->emplacement_id]),
            ))->lot),
        ];
    }

    /**
     * Écart d'une pièce numérotée (null si elle est conforme).
     *
     * @return ?array<string, mixed>
     */
    private static function ecartPiece(InventaireLigne $ligne, InventaireEmplacement $noeud): ?array
    {
        $nature = match (true) {
            $ligne->origine === 'EN_PLUS' => 'plus',
            $ligne->statut === 'ABSENT' => 'manq',
            $ligne->statut === 'DEFECTUEUX' => 'def',
            default => null,
        };
        if ($nature === null) {
            return null;
        }

        // Une pièce trouvée sans article en stock a pu être créée depuis, sous ce numéro
        $article = $ligne->article
            ?? Article::nonSupprimes()->where('numero', $ligne->numero)->first();
        $ici = $article !== null && $article->supprime_le === null
            && $noeud->emplacement_id !== null && $article->emplacement_id === $noeud->emplacement_id;

        [$resolution, $proposition] = match (true) {
            // Trouvée ici : à créer, déjà supprimée ou emplacement supprimé depuis, déjà rangée ici, ou à déplacer
            $nature === 'plus' && $article === null => [null, 'CREER'],
            $nature === 'plus' && ($article->supprime_le !== null || $noeud->emplacement_id === null) => ['PLUS_CONCERNE', null],
            $nature === 'plus' => $ici ? ['A_JOUR', null] : [null, 'DEPLACER'],
            // Manquante ou défectueuse : déjà supprimée, encore ici, ou partie ailleurs depuis
            $article === null || $article->supprime_le !== null => ['A_JOUR', null],
            default => $ici ? [null, 'SUPPRIMER'] : ['PLUS_CONCERNE', null],
        };
        $etatArticle = $article === null ? null : [
            'id' => $article->id,
            'emplacement_id' => $article->emplacement_id,
            'sapeur_id' => $article->sapeur_id,
            'supprime_le' => $article->supprime_le?->toDateString(),
        ];

        return self::ecart("piece-{$ligne->id}", $nature, collect([$ligne]), $noeud->id, $resolution, $proposition, [
            'est_lot' => false,
            'prevu' => $ligne->origine === 'ATTENDU' ? 1 : 0,
            'constate' => $ligne->statut === 'ABSENT' ? 0 : 1,
            'actuel' => $ici ? 1 : 0,
            'article_actuel' => $etatArticle,
            'quantite' => 1,
            'article_ids_a_retirer' => $proposition === 'SUPPRIMER' ? [$article->id] : [],
        ]);
    }

    /**
     * Écarts d'un lot : la quantité (manquantes ou en plus), puis les défectueuses.
     *
     * @param Collection<int, InventaireLigne> $lot
     * @param Collection<int, Article> $connus articles en stock référencés par l'inventaire, par id
     * @param Collection<int, Article> $nouveaux articles de même clé ajoutés depuis l'inventaire
     * @return list<array<string, mixed>>
     */
    private static function ecartsLot(Collection $lot, InventaireEmplacement $noeud, Collection $connus, Collection $nouveaux): array
    {
        $id = $lot->first()->id;
        $absentes = $lot->where('statut', 'ABSENT');
        $enPlus = $lot->where('origine', 'EN_PLUS');
        $defectueuses = $lot->where('statut', 'DEFECTUEUX');
        $constate = $lot->where('statut', '!=', 'ABSENT')->count();
        // Pièces du lot encore dans l'emplacement, même modifiées depuis (compartiment, taille…)
        // (only() d'une collection Eloquent renvoie une liste : on réindexe par id)
        $enPlace = $connus->only($lot->pluck('article_id')->filter()->all())
            ->keyBy('id')
            ->filter(fn (Article $article) => $article->emplacement_id === $noeud->emplacement_id);
        $commun = [
            'est_lot' => true,
            'prevu' => $lot->where('origine', 'ATTENDU')->count(),
            'constate' => $constate,
            'actuel' => $enPlace->count() + $nouveaux->count(),
            'article_actuel' => null,
            // Ce qui a changé depuis l'inventaire, hors retraits décidés par ce traitement
            'ajoutes_depuis' => $nouveaux->count(),
            'partis_depuis' => $lot->filter(fn (InventaireLigne $ligne) => $ligne->article_id !== null
                && $ligne->decision !== 'SORTIR_DU_STOCK'
                && !$enPlace->has($ligne->article_id))->count(),
        ];
        $ecarts = [];

        if ($absentes->isNotEmpty() || $enPlus->isNotEmpty()) {
            $retirees = $defectueuses->where('decision', 'SORTIR_DU_STOCK')->count();
            // Manque (> 0) ou surplus (< 0) par rapport aux pièces que l'inventaire a comptées ;
            // les pièces ajoutées depuis comblent le manque, elles ne se retirent pas.
            $manque = $constate - $retirees - $enPlace->count();
            $ecartStock = $manque > 0 ? max(0, $manque - $nouveaux->count()) : $manque;
            // Pièces à retirer : celles relevées manquantes d'abord, jamais une défectueuse
            $absentIds = $absentes->pluck('article_id')->filter()->all();
            $defectueusesIds = $defectueuses->pluck('article_id')->filter()->all();
            $retirables = $enPlace->reject(fn (Article $a) => in_array($a->id, $defectueusesIds, true))
                ->sortBy(fn (Article $a) => in_array($a->id, $absentIds, true) ? 0 : 1)
                ->values();
            [$resolution, $proposition] = match (true) {
                // Emplacement supprimé depuis l'inventaire : plus rien à aligner ici
                $noeud->emplacement_id === null => ['PLUS_CONCERNE', null],
                $ecartStock === 0 => ['A_JOUR', null],
                default => [null, $ecartStock < 0 ? 'RETIRER' : 'AJOUTER'],
            };
            $ecarts[] = self::ecart("lot-{$id}-quantite", $absentes->isNotEmpty() ? 'manq' : 'plus', $absentes->merge($enPlus), $noeud->id, $resolution, $proposition, [
                ...$commun,
                'quantite' => abs($ecartStock),
                'article_ids_a_retirer' => $ecartStock < 0 ? $retirables->take(-$ecartStock)->pluck('id')->all() : [],
            ]);
        }

        if ($defectueuses->isNotEmpty()) {
            $encoreLa = $defectueuses->pluck('article_id')->filter(fn (?int $articleId) => $enPlace->has($articleId))->values()->all();
            // Plus aucune défectueuse dans l'emplacement : elles ont bougé depuis l'inventaire
            [$resolution, $proposition] = $encoreLa === [] ? ['PLUS_CONCERNE', null] : [null, 'RETIRER'];
            $ecarts[] = self::ecart("lot-{$id}-def", 'def', $defectueuses, $noeud->id, $resolution, $proposition, [
                ...$commun,
                'quantite' => count($encoreLa),
                'article_ids_a_retirer' => $encoreLa,
            ]);
        }

        return $ecarts;
    }

    /**
     * @param Collection<int, InventaireLigne> $lignes lignes qui recevront la décision
     * @param array<string, mixed> $details
     * @return array<string, mixed>
     */
    private static function ecart(string $id, string $nature, Collection $lignes, int $noeudId, ?string $resolution, ?string $proposition, array $details): array
    {
        $ligne = $lignes->first();
        $decisions = match ($proposition) {
            'SUPPRIMER', 'RETIRER' => ['SORTIR_DU_STOCK', 'AUCUNE_ACTION'],
            'AJOUTER', 'CREER', 'DEPLACER' => ['AJOUTER_AU_STOCK', 'AUCUNE_ACTION'],
            default => ['AUCUNE_ACTION'],
        };
        // Ce dont dépend la proposition : si le stock bouge, la quantité ou les pièces à retirer changent
        $etat = [$resolution, $proposition, $details['quantite'], $details['article_actuel'], $details['article_ids_a_retirer']];

        return [
            'id' => $id,
            'nature' => $nature,
            'noeud_id' => $noeudId,
            'ligne_ids' => $lignes->pluck('id')->all(),
            ...$ligne->only(['designation', 'numero', 'materiel_type_id', 'compartiment', 'taille', 'est_etiquete', 'article_id', 'origine']),
            'date_peremption' => $ligne->date_peremption?->toDateString(),
            'precision' => $lignes->pluck('remarque_sapeur')->filter()->first(),
            'resolution' => $resolution,
            'proposition' => $proposition,
            'decisions' => $decisions,
            'signature' => md5(json_encode($etat)),
            ...$ligne->only(['decision', 'traite_par_sapeur_id', 'remarque_traitement']),
            'traite_le' => $ligne->traite_le?->toIso8601String(),
            ...$details,
        ];
    }

    /**
     * Range l'article trouvé ici, ou crée les pièces manquantes au stock.
     *
     * @param array<string, mixed> $ecart
     * @param array<string, mixed> $commun
     * @param list<array<string, mixed>> $articles
     * @throws ArrayException si le type manque ou ne convient pas, ou si un numéro est en double
     */
    private static function ajouterAuStock(array $ecart, ?int $materielTypeId, array $commun, array $articles, ?int $auteurSapeurId): void
    {
        $lignes = InventaireLigne::whereIn('id', $ecart['ligne_ids'])->with('inventaireEmplacement')->orderBy('id')->get();
        $ligne = $lignes->first();
        $noeud = $ligne->inventaireEmplacement;
        $emplacementTrouve = $noeud->emplacement_id;

        if ($ecart['proposition'] === 'DEPLACER') {
            $article = Article::findOrFail($ecart['article_actuel']['id']);
            EmplacementBusiness::assertEmplacementActif($emplacementTrouve);
            $compartimentable = (bool) Emplacement::findOrFail($emplacementTrouve)->est_compartimentable;
            self::ranger($article, $emplacementTrouve, $compartimentable ? $ligne->compartiment : '', $auteurSapeurId);
            $ligne->update(['article_id' => $article->id]);

            return;
        }

        // Le type choisi par le responsable corrige celui de la ligne
        $typeId = $materielTypeId ?? $ligne->materiel_type_id;
        if ($typeId === null) {
            throw new ArrayException([], "Choisissez le type de « {$ligne->designation} » avant de l'ajouter au stock");
        }
        $type = MaterielType::findOrFail($typeId);
        if ($type->est_emplacement) {
            throw new ArrayException([], "Un véhicule ne s'ajoute pas depuis un inventaire : créez-le depuis les paramètres du matériel");
        }

        // Une pièce par manque : les pièces trouvées en plus servent de modèle, puis la première ligne
        $modeles = $lignes->where('origine', 'EN_PLUS')->values();
        $numeros = [];
        $creees = [];
        for ($index = 0; $index < $ecart['quantite']; $index++) {
            $modele = $modeles[$index] ?? $ligne;
            $piece = [...$commun, ...($articles[$index] ?? [])];
            // Rangé où il a été trouvé, sauf si le responsable choisit un autre emplacement
            $emplacementId = isset($piece['emplacement_id']) ? (int) $piece['emplacement_id'] : $emplacementTrouve;
            if ($emplacementId === null) {
                throw new ArrayException([], "L'emplacement « {$noeud->designation} » a été supprimé depuis l'inventaire");
            }
            EmplacementBusiness::assertEmplacementActif($emplacementId);
            // Le compartiment relevé n'a de sens que dans un emplacement compartimenté
            $compartimentable = (bool) Emplacement::findOrFail($emplacementId)->est_compartimentable;

            $numero = trim($piece['numero'] ?? $modele->numero ?? '');
            if ($type->est_numerote && $numero !== '' && in_array($numero, $numeros, true)) {
                throw new ArrayException([], "Le numéro {$numero} est donné à plusieurs pièces : chaque pièce numérotée a son propre numéro");
            }
            $numeros[] = $numero;

            [$creees[]] = ArticleBusiness::creerArticles([[
                'materiel_type_id' => $type->id,
                'emplacement_id' => $emplacementId,
                'numero' => $numero,
                'compartiment' => $compartimentable ? trim($piece['compartiment'] ?? $modele->compartiment) : '',
                'est_etiquete' => $piece['est_etiquete'] ?? $modele->est_etiquete,
                // Ce que le sapeur a relevé sur l'objet, sauf correction du responsable
                'designation' => trim($piece['designation'] ?? ''),
                'taille' => $piece['taille'] ?? $modele->taille ?? '',
                'date_peremption' => $piece['date_peremption'] ?? $modele->date_peremption?->toDateString(),
                'achat' => trim($piece['achat'] ?? ''),
                'remarque' => trim($piece['remarque'] ?? ''),
                'disponible_intervention' => $piece['disponible_intervention'] ?? true,
            ]], $auteurSapeurId);
        }

        // Les pièces trouvées en plus pointent vers l'article créé pour elles. Elles gardent le
        // type relevé par le sapeur : il fait partie de la clé du lot.
        foreach ($modeles as $index => $enPlus) {
            $enPlus->update(['article_id' => $creees[$index]->id ?? null]);
        }
    }

    /**
     * Range un article existant dans l'emplacement où il a été trouvé, dans le compartiment
     * relevé (vide hors emplacement compartimenté) ; une attribution en cours est close.
     */
    private static function ranger(Article $article, int $emplacementId, string $compartiment, ?int $auteurSapeurId): void
    {
        if ($article->materielType->est_emplacement) {
            throw new ArrayException([], "Un véhicule ne se range pas dans un emplacement : déplacez-le depuis sa fiche");
        }

        if ($article->sapeur_id !== null) {
            ArticleBusiness::retourArticles($emplacementId, Carbon::today()->toDateString(), [$article->id], $auteurSapeurId);
        }

        $article->update(['emplacement_id' => $emplacementId, 'compartiment' => $compartiment]);
    }
}
