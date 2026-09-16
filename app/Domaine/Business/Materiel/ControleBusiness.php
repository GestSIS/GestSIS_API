<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleMaterielType;
use App\Models\ControleTache;
use App\Models\ControleExec;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ControleBusiness
{
    /**
     * @param array{
     *   nom: string,
     *   description: ?string,
     *   recurrence_type: string,
     *   recurrence_value: ?int,
     *   duree_preavis: ?int,
     *   externe: ?bool,
     *   reparateur: ?string,
     *   nb_execution_max: ?int,
     *   nb_execution_preavis: ?int,
     *   taches: array<int, array{id?: int, order: int, nom: string, description: ?string, type: string, unit: ?string, value_min: ?float, value_max: ?float}>,
     *   materiel_type_ids: int[]
     * } $data
     */
    public static function createControle(array $data): Controle
    {
        self::validateRecurrence($data['recurrence_type'], $data['recurrence_value'] ?? null);
        self::validateDureePreavis($data['recurrence_type'], $data['duree_preavis'] ?? null);
        self::validateNbExecutionMax($data['recurrence_type'], $data['nb_execution_max'] ?? null, $data['nb_execution_preavis'] ?? null);

        return DB::transaction(function () use ($data): Controle {
            $controle = Controle::create([
                'nom'                  => $data['nom'],
                'description'          => $data['description'] ?? null,
                'recurrence_type'      => $data['recurrence_type'],
                'recurrence_value'     => $data['recurrence_value'] ?? null,
                'duree_preavis'        => $data['duree_preavis'] ?? null,
                'externe'              => $data['externe'] ?? false,
                'reparateur'           => $data['reparateur'] ?? null,
                'nb_execution_max'     => $data['nb_execution_max'] ?? null,
                'nb_execution_preavis' => $data['nb_execution_preavis'] ?? null,
            ]);

            self::syncTaches($controle, $data['taches'] ?? []);
            self::syncMaterielTypes($controle, $data['materiel_type_ids'] ?? []);

            return Controle::with(['taches', 'materielTypes.materielType'])->findOrFail($controle->id);
        });
    }

    /**
     * @param array{
     *   nom: string,
     *   description: ?string,
     *   recurrence_type: string,
     *   recurrence_value: ?int,
     *   duree_preavis: ?int,
     *   externe: ?bool,
     *   reparateur: ?string,
     *   nb_execution_max: ?int,
     *   nb_execution_preavis: ?int,
     *   taches: array<int, array{id?: int, order: int, nom: string, description: ?string, type: string, unit: ?string, value_min: ?float, value_max: ?float}>,
     *   materiel_type_ids: int[]
     * } $data
     */
    public static function editControle(int $id, array $data): Controle
    {
        self::validateRecurrence($data['recurrence_type'], $data['recurrence_value'] ?? null);
        self::validateDureePreavis($data['recurrence_type'], $data['duree_preavis'] ?? null);
        self::validateNbExecutionMax($data['recurrence_type'], $data['nb_execution_max'] ?? null, $data['nb_execution_preavis'] ?? null);

        return DB::transaction(function () use ($id, $data): Controle {
            $controle = Controle::findOrFail($id);

            $controle->update([
                'nom'                  => $data['nom'],
                'description'          => $data['description'] ?? null,
                'recurrence_type'      => $data['recurrence_type'],
                'recurrence_value'     => $data['recurrence_value'] ?? null,
                'duree_preavis'        => $data['duree_preavis'] ?? null,
                'externe'              => $data['externe'] ?? false,
                'reparateur'           => $data['reparateur'] ?? null,
                'nb_execution_max'     => $data['nb_execution_max'] ?? null,
                'nb_execution_preavis' => $data['nb_execution_preavis'] ?? null,
            ]);

            self::syncTaches($controle, $data['taches'] ?? []);
            self::syncMaterielTypes($controle, $data['materiel_type_ids'] ?? []);

            return Controle::with(['taches', 'materielTypes.materielType'])->findOrFail($id);
        });
    }

    public static function deleteControle(int $id): bool
    {
        if (ControleExec::where('controle_id', $id)->exists()) {
            throw new ArrayException([], "Impossible de supprimer un contrôle ayant des exécutions enregistrées");
        }

        return (bool) Controle::whereId($id)->delete();
    }

    /**
     * @param array<int, array{id?: int, order: int, nom: string, description: ?string, type: string, unit: ?string, value_min: ?float, value_max: ?float}> $tachesData
     */
    private static function syncTaches(Controle $controle, array $tachesData): void
    {
        $submittedIds = collect($tachesData)->pluck('id')->filter()->values()->all();

        $query = ControleTache::where('controle_id', $controle->id);
        if ($submittedIds !== []) {
            $query->whereNotIn('id', $submittedIds);
        }
        $query->delete();

        foreach ($tachesData as $tacheData) {
            $isNumeric = $tacheData['type'] === 'NUMERIC';
            $payload = [
                'controle_id' => $controle->id,
                'order'       => $tacheData['order'],
                'nom'         => $tacheData['nom'],
                'description' => $tacheData['description'] ?? null,
                'type'        => $tacheData['type'],
                'unit'        => $isNumeric ? ($tacheData['unit'] ?? null) : null,
                'value_min'   => $isNumeric ? ($tacheData['value_min'] ?? null) : null,
                'value_max'   => $isNumeric ? ($tacheData['value_max'] ?? null) : null,
            ];

            if (($tacheData['id'] ?? null) !== null) {
                ControleTache::where('controle_id', $controle->id)
                    ->whereId($tacheData['id'])
                    ->update($payload);
            } else {
                ControleTache::create($payload);
            }
        }
    }

    /**
     * @param int[] $typeIds
     */
    private static function syncMaterielTypes(Controle $controle, array $typeIds): void
    {
        ControleMaterielType::where('controle_id', $controle->id)->delete();

        // Un type ne peut être rattaché qu'une fois à un contrôle (contrainte
        // d'unicité en base) : un doublon dans le payload est simplement ignoré.
        foreach (array_unique($typeIds) as $typeId) {
            ControleMaterielType::create([
                'controle_id'      => $controle->id,
                'materiel_type_id' => $typeId,
            ]);
        }
    }

    private static function validateRecurrence(string $type, ?int $value): void
    {
        match ($type) {
            'PERIODIQUE' => $value === null || $value <= 0
                ? throw new ArrayException([], "La récurrence PERIODIQUE doit être un entier positif (nombre de mois)")
                : null,
            'NON_PERIODIQUE' => $value !== null
                ? throw new ArrayException([], "Le type de récurrence {$type} ne doit pas avoir de valeur associée")
                : null,
            default => throw new ArrayException([], "Type de récurrence invalide : {$type}"),
        };
    }

    private static function validateDureePreavis(string $type, ?int $value): void
    {
        if ($value === null) {
            return;
        }
        if ($type !== 'PERIODIQUE') {
            throw new ArrayException([], "Le délai de préavis n'est utilisable que pour une récurrence PERIODIQUE");
        }
        if ($value <= 0) {
            throw new ArrayException([], "Le délai de préavis doit être un entier positif (nombre de mois)");
        }
    }

    private static function validateNbExecutionMax(string $type, ?int $max, ?int $preavis): void
    {
        if ($max === null && $preavis === null) {
            return;
        }
        if ($type === 'PERIODIQUE') {
            throw new ArrayException([], "Le nombre d'exécutions maximum n'est utilisable que pour une récurrence NON_PERIODIQUE");
        }
        if ($max !== null && $max <= 0) {
            throw new ArrayException([], "Le nombre d'exécutions maximum doit être un entier positif");
        }
        if ($preavis !== null && $max === null) {
            throw new ArrayException([], "Le préavis en nombre d'exécutions nécessite un nombre d'exécutions maximum");
        }
        if ($preavis !== null && $preavis <= 0) {
            throw new ArrayException([], "Le préavis en nombre d'exécutions doit être un entier positif");
        }
        if ($preavis !== null && $max !== null && $preavis > $max) {
            throw new ArrayException([], "Le préavis en nombre d'exécutions ne peut pas dépasser le nombre d'exécutions maximum");
        }
    }

    /**
     * Contrôles pour lesquels un article peut être considéré « à contrôler » :
     * PERIODIQUE (échéance par date) ou NON_PERIODIQUE avec un nombre
     * d'exécutions maximum configuré (usage, ex: lavages).
     *
     * @return \Illuminate\Support\Collection<int, Controle>
     */
    private static function controlesSuivis()
    {
        return Controle::where('recurrence_type', 'PERIODIQUE')
            ->orWhereNotNull('nb_execution_max')
            ->with('materielTypes')
            ->get();
    }

    /**
     * Statut d'un article pour un contrôle donné, à partir de l'agrégat de ses
     * exécutions : "danger" (échéance ou nombre d'exécutions dépassé),
     * "warning" (préavis, ou jamais contrôlé pour un contrôle PERIODIQUE) ou
     * null (rien à signaler).
     *
     * Pour un contrôle PERIODIQUE, l'échéance est celle enregistrée sur la
     * dernière exécution (voir ControleExecBusiness::resolveDateEcheance) —
     * pas recalculée ici — ce qui permet à un contrôle de récurrence variable
     * (ex : premier service véhicule à 5 ans, puis tous les 2 ans) d'avoir une
     * échéance différente de la récurrence par défaut du contrôle.
     *
     * Publique : réutilisée par ControleExecBusiness::getDernieresExecutionsParArticle
     * pour ne pas dupliquer ce calcul.
     */
    public static function statutArticlePourControle(Controle $controle, ?object $execArticle, Carbon $maintenant): ?string
    {
        if ($controle->recurrence_type === 'PERIODIQUE') {
            $dateEcheance = $execArticle?->date_echeance;

            if ($dateEcheance === null) {
                return 'warning';
            }

            $echeance = Carbon::parse($dateEcheance);
            if ($maintenant->greaterThanOrEqualTo($echeance)) {
                return 'danger';
            }
            if (
                $controle->duree_preavis !== null &&
                $maintenant->greaterThanOrEqualTo($echeance->copy()->subMonths($controle->duree_preavis))
            ) {
                return 'warning';
            }

            return null;
        }

        if ($controle->nb_execution_max !== null) {
            $nbExecutions = $execArticle?->nb_executions ?? 0;

            if ($nbExecutions >= $controle->nb_execution_max) {
                return 'danger';
            }
            if ($controle->nb_execution_preavis !== null && $nbExecutions >= $controle->nb_execution_preavis) {
                return 'warning';
            }
        }

        return null;
    }

    /**
     * Dernière exécution par (contrôle, article) parmi les contrôles donnés :
     * date, nombre total d'exécutions, et échéance enregistrée sur cette
     * dernière exécution (renseignée uniquement pour un contrôle PERIODIQUE).
     *
     * @param \Illuminate\Support\Collection<int, int> $controleIds
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, object{article_id: int, derniere_execution: string, date_echeance: ?string, nb_executions: int}>> indexé par controle_id puis article_id
     */
    private static function dernieresExecutionsParControle($controleIds)
    {
        return ControleExec::whereIn('controle_id', $controleIds)
            ->get(['controle_id', 'article_id', 'executed_at', 'date_echeance'])
            ->groupBy('controle_id')
            ->map(fn ($execs) => $execs
                ->groupBy('article_id')
                ->map(function ($execsArticle) {
                    $derniere = $execsArticle->sortByDesc('executed_at')->first();

                    return (object) [
                        'article_id'         => $derniere->article_id,
                        'derniere_execution' => $derniere->executed_at->toDateTimeString(),
                        'date_echeance'      => $derniere->date_echeance?->toDateString(),
                        'nb_executions'      => $execsArticle->count(),
                    ];
                }));
    }

    /**
     * Pour chaque contrôle suivi (PERIODIQUE, ou usage avec un nombre
     * d'exécutions maximum), compte les articles actifs en retard/dépassés et
     * en préavis. Calculé à la demande en quelques requêtes agrégées (pas de
     * requête par article).
     *
     * @return array<int, array{controle_id: int, nb_en_retard: int, nb_en_preavis: int}>
     */
    public static function calculerStatuts(): array
    {
        $controles = self::controlesSuivis();

        if ($controles->isEmpty()) {
            return [];
        }

        $materielTypeIds = $controles
            ->flatMap(fn (Controle $c) => $c->materielTypes->pluck('materiel_type_id'))
            ->unique();

        $articlesParType = Article::whereIn('materiel_type_id', $materielTypeIds)
            ->where('statut', true)
            ->get(['id', 'materiel_type_id'])
            ->groupBy('materiel_type_id');

        $execsParControle = self::dernieresExecutionsParControle($controles->pluck('id'));

        $maintenant = Carbon::now();

        return $controles->map(function (Controle $controle) use ($articlesParType, $execsParControle, $maintenant): array {
            $articleIds = $controle->materielTypes
                ->flatMap(fn (ControleMaterielType $mt) => $articlesParType->get($mt->materiel_type_id, collect())->pluck('id'))
                ->unique();

            $execsControle = ($execsParControle->get($controle->id) ?? collect())->keyBy('article_id');

            $nbEnRetard = 0;
            $nbEnPreavis = 0;

            foreach ($articleIds as $articleId) {
                $statut = self::statutArticlePourControle($controle, $execsControle->get($articleId), $maintenant);

                if ($statut === 'danger') {
                    $nbEnRetard++;
                } elseif ($statut === 'warning') {
                    $nbEnPreavis++;
                }
            }

            return [
                'controle_id'   => $controle->id,
                'nb_en_retard'  => $nbEnRetard,
                'nb_en_preavis' => $nbEnPreavis,
            ];
        })->values()->all();
    }

    /**
     * Détail des articles actifs en retard ou en préavis pour les contrôles
     * PERIODIQUE (échéance par date), groupés par contrôle. Ne renvoie que
     * les contrôles ayant au moins un article concerné. Les contrôles à
     * nombre d'exécutions maximum (usage, ex: lavages) sont exclus — voir
     * getArticlesLimiteExecutions().
     *
     * @return array<int, array{controle_id: int, nom: string, articles: array<int, array>}>
     */
    public static function getArticlesAControler(): array
    {
        $controles = Controle::where('recurrence_type', 'PERIODIQUE')->with('materielTypes')->get();

        return self::buildArticlesAControlerPourControles($controles);
    }

    /**
     * Détail des articles actifs ayant dépassé ou approchant le nombre
     * d'exécutions maximum configuré sur leur contrôle (NON_PERIODIQUE,
     * usage — ex: lavages), groupés par contrôle. Ne renvoie que les
     * contrôles ayant au moins un article concerné.
     *
     * @return array<int, array{controle_id: int, nom: string, articles: array<int, array>}>
     */
    public static function getArticlesLimiteExecutions(): array
    {
        $controles = Controle::whereNotNull('nb_execution_max')->with('materielTypes')->get();

        return self::buildArticlesAControlerPourControles($controles);
    }

    /**
     * @param \Illuminate\Support\Collection<int, Controle> $controles
     * @return array<int, array{controle_id: int, nom: string, articles: array<int, array>}>
     */
    private static function buildArticlesAControlerPourControles($controles): array
    {
        if ($controles->isEmpty()) {
            return [];
        }

        $materielTypeIds = $controles
            ->flatMap(fn (Controle $c) => $c->materielTypes->pluck('materiel_type_id'))
            ->unique();

        $articlesParType = Article::whereIn('materiel_type_id', $materielTypeIds)
            ->where('statut', true)
            ->with(['materielType', 'sapeur', 'emplacement'])
            ->get()
            ->groupBy('materiel_type_id');

        $execsParControle = self::dernieresExecutionsParControle($controles->pluck('id'));

        $maintenant = Carbon::now();

        return $controles->map(function (Controle $controle) use ($articlesParType, $execsParControle, $maintenant): ?array {
            $articles = $controle->materielTypes
                ->flatMap(fn (ControleMaterielType $mt) => $articlesParType->get($mt->materiel_type_id, collect()))
                ->unique('id');

            $execsControle = ($execsParControle->get($controle->id) ?? collect())->keyBy('article_id');

            $articlesAControler = $articles->map(function (Article $article) use ($execsControle, $controle, $maintenant): ?array {
                $execArticle = $execsControle->get($article->id);
                $statut = self::statutArticlePourControle($controle, $execArticle, $maintenant);

                if ($statut === null) {
                    return null;
                }

                return [
                    'id'                   => $article->id,
                    'materiel_type_id'     => $article->materiel_type_id,
                    'numero'               => $article->numero,
                    'designation'          => $article->designation,
                    'type_designation'     => $article->materielType->designation,
                    'sapeur'               => $article->sapeur ? "{$article->sapeur->nom} {$article->sapeur->prenom}" : null,
                    'emplacement'          => $article->emplacement?->designation,
                    'derniere_execution'   => $execArticle?->derniere_execution ?? null,
                    'prochaine_execution'  => $execArticle?->date_echeance ?? null,
                    'nb_executions'        => $execArticle?->nb_executions ?? 0,
                    'nb_execution_max'     => $controle->nb_execution_max,
                    'statut'               => $statut,
                ];
            })->filter()->values();

            if ($articlesAControler->isEmpty()) {
                return null;
            }

            return [
                'controle_id' => $controle->id,
                'nom'         => $controle->nom,
                'articles'    => $articlesAControler->all(),
            ];
        })->filter()->values()->all();
    }
}
