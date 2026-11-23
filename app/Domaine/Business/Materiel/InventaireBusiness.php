<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Inventaires du matériel : démarrage sur une branche d'emplacements.
 *
 * Un seul inventaire en cours par branche : un emplacement ne peut pas être inventorié
 * si lui-même, un de ses ancêtres ou un de ses descendants fait déjà partie d'un
 * inventaire en cours. La règle est garantie ici (lignes d'emplacements verrouillées
 * pendant la vérification), pas par la base.
 */
class InventaireBusiness
{
    /**
     * Inventaires du plus récent au plus ancien, avec leur racine et leurs compteurs :
     * lignes, lignes vérifiées, écarts et écarts encore à traiter.
     *
     * @return Collection<int, Inventaire>
     */
    public static function liste(): Collection
    {
        return Inventaire::with('racine')
            ->withCount([
                'lignes',
                'lignes as lignes_verifiees_count' => fn (Builder $query) => $query->whereNotNull('inventaire_lignes.statut'),
                'lignes as ecarts_count' => fn (Builder $query) => $query->ecarts(),
                'lignes as ecarts_a_traiter_count' => fn (Builder $query) => $query->ecarts()->whereNull('inventaire_lignes.decision'),
            ])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Démarre un inventaire sur l'emplacement donné : fige le périmètre (l'emplacement
     * et ses sous-emplacements actifs) et crée une ligne attendue par article non
     * supprimé rangé dans ce périmètre.
     *
     * @throws ArrayException si la date est future, si l'emplacement est inactif ou si un
     *                        inventaire est déjà en cours sur sa branche
     */
    public static function demarrer(int $emplacementId, string $designation, string $date, ?int $auteurSapeurId): Inventaire
    {
        $inventoryDate = Carbon::parse($date)->startOfDay();
        if ($inventoryDate->isAfter(Carbon::today())) {
            throw new ArrayException([], "La date de l'inventaire ne peut pas être dans le futur");
        }

        return DB::transaction(function () use ($emplacementId, $designation, $inventoryDate, $auteurSapeurId) {
            $racine = Emplacement::findOrFail($emplacementId);
            if (!$racine->statut) {
                throw new ArrayException([], "L'emplacement « {$racine->designation} » est inactif, il ne peut pas être inventorié");
            }

            $branchIds = array_values(array_unique([
                ...EmplacementBusiness::ancestorIds($emplacementId),
                ...EmplacementBusiness::descendantIds($emplacementId),
            ]));
            Emplacement::whereIn('id', $branchIds)->lockForUpdate()->get();
            self::assertAucunInventaireEnCours($branchIds);

            $inventaire = Inventaire::create([
                'designation' => $designation,
                'date' => $inventoryDate->toDateString(),
                'statut' => 'EN_COURS',
                'demarre_par_sapeur_id' => $auteurSapeurId,
            ]);

            $nodeIdByEmplacementId = self::figerPerimetre($inventaire, $racine);
            self::creerLignesAttendues($nodeIdByEmplacementId);

            return $inventaire->load(['emplacements', 'lignes']);
        });
    }

    /**
     * Valide un inventaire en cours : la saisie est terminée et n'est plus modifiable, la
     * branche est libérée pour un nouvel inventaire. Le stock n'est pas touché : les écarts
     * restent à traiter par le responsable matériel.
     *
     * @throws ArrayException si l'inventaire est déjà validé ou s'il reste des pièces à vérifier
     */
    public static function valider(int $inventaireId, ?string $remarque, ?int $auteurSapeurId): Inventaire
    {
        return DB::transaction(function () use ($inventaireId, $remarque, $auteurSapeurId) {
            $inventaire = Inventaire::lockForUpdate()->findOrFail($inventaireId);
            if ($inventaire->statut !== 'EN_COURS') {
                throw new ArrayException([], "L'inventaire « {$inventaire->designation} » est déjà validé");
            }

            $aVerifier = $inventaire->lignes()->whereNull('inventaire_lignes.statut')->count();
            if ($aVerifier > 0) {
                $pieces = $aVerifier > 1 ? "{$aVerifier} pièces" : '1 pièce';
                throw new ArrayException([], "Il reste {$pieces} à vérifier avant de valider l'inventaire");
            }

            $inventaire->update([
                'statut' => 'VALIDE',
                'valide_par_sapeur_id' => $auteurSapeurId,
                'valide_le' => Carbon::now(),
                'remarque' => $remarque === null || trim($remarque) === '' ? null : trim($remarque),
            ]);

            return $inventaire->load(['emplacements', 'lignes']);
        });
    }

    /**
     * Supprime un inventaire en cours que l'on ne veut pas terminer, avec son périmètre et
     * sa saisie. La branche est libérée pour un nouvel inventaire.
     *
     * @throws ArrayException si l'inventaire est validé
     */
    public static function supprimer(int $inventaireId): void
    {
        DB::transaction(function () use ($inventaireId) {
            $inventaire = Inventaire::lockForUpdate()->findOrFail($inventaireId);
            if ($inventaire->statut !== 'EN_COURS') {
                throw new ArrayException([], "L'inventaire « {$inventaire->designation} » est validé, il ne peut plus être supprimé");
            }

            $inventaire->delete();
        });
    }

    /**
     * @param int[] $emplacementIds emplacement, ses ancêtres et ses descendants
     * @throws ArrayException si un de ces emplacements fait partie d'un inventaire en cours
     */
    private static function assertAucunInventaireEnCours(array $emplacementIds): void
    {
        $noeudEnCours = InventaireEmplacement::whereIn('emplacement_id', $emplacementIds)
            ->whereHas('inventaire', fn ($query) => $query->where('statut', 'EN_COURS'))
            ->with('inventaire')
            ->first();

        if ($noeudEnCours !== null) {
            throw new ArrayException([], "L'inventaire « {$noeudEnCours->inventaire->designation} » est déjà en cours sur cette branche d'emplacements");
        }
    }

    /**
     * Copie l'emplacement racine et ses sous-emplacements actifs dans inventaire_emplacements.
     * Un sous-emplacement inactif est écarté avec toute sa branche : il ne contient plus
     * que du matériel supprimé.
     *
     * @return array<int, int> id du nœud figé, indexé par id d'emplacement
     */
    private static function figerPerimetre(Inventaire $inventaire, Emplacement $racine): array
    {
        $activeChildrenByParent = Emplacement::where('statut', true)
            ->get(['id', 'parent_id', 'designation', 'tri'])
            ->groupBy('parent_id');

        $nodeIdByEmplacementId = [];
        $queue = [[$racine, null]];
        while ($queue !== []) {
            [$emplacement, $parentNodeId] = array_shift($queue);

            $node = InventaireEmplacement::create([
                'inventaire_id' => $inventaire->id,
                'parent_id' => $parentNodeId,
                'emplacement_id' => $emplacement->id,
                'designation' => $emplacement->designation,
                'tri' => $emplacement->tri,
            ]);
            $nodeIdByEmplacementId[$emplacement->id] = $node->id;

            foreach ($activeChildrenByParent->get($emplacement->id, collect()) as $child) {
                $queue[] = [$child, $node->id];
            }
        }

        return $nodeIdByEmplacementId;
    }

    /**
     * Une ligne ATTENDU par article non supprimé rangé dans le périmètre, avec ses
     * valeurs figées. Les articles attribués à un sapeur n'ont pas d'emplacement et ne
     * sont donc pas inventoriés ; l'article d'un véhicule est représenté par son
     * emplacement, pas par une ligne.
     *
     * @param array<int, int> $nodeIdByEmplacementId
     */
    private static function creerLignesAttendues(array $nodeIdByEmplacementId): void
    {
        $articles = Article::nonSupprimes()
            ->whereIn('emplacement_id', array_keys($nodeIdByEmplacementId))
            ->with('materielType')
            ->get();

        foreach ($articles as $article) {
            InventaireLigne::create([
                ...self::ligneAttendue($article, $nodeIdByEmplacementId[$article->emplacement_id]),
                'origine' => 'ATTENDU',
                'statut' => null,
            ]);
        }
    }

    /**
     * Valeurs figées d'une pièce en stock dans un nœud de l'inventaire. Sert au démarrage
     * et, au traitement des écarts, à retrouver le lot d'un article du stock actuel.
     *
     * @return array<string, mixed>
     */
    public static function ligneAttendue(Article $article, int $noeudId): array
    {
        return [
            'inventaire_emplacement_id' => $noeudId,
            'article_id' => $article->id,
            'materiel_type_id' => $article->materiel_type_id,
            'numero' => trim((string) $article->numero) === '' ? null : $article->numero,
            'designation' => InventaireSaisieBusiness::designationArticle($article),
            'compartiment' => $article->compartiment,
            // Variante du lot : deux pièces de taille ou de péremption différentes se comptent à part
            'taille' => trim((string) $article->taille) === '' ? null : trim($article->taille),
            'date_peremption' => $article->date_peremption?->toDateString(),
            'est_etiquete' => (bool) $article->est_etiquete,
        ];
    }
}
