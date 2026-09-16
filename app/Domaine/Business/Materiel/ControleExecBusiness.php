<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleTache;
use App\Models\ControleExec;
use App\Models\ControleExecTache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ControleExecBusiness
{
    /**
     * Soumet une exécution complète (toutes les tâches du contrôle doivent être
     * renseignées — un contrôle sans tâche s'enregistre avec une liste vide).
     *
     * @param array{
     *   executed_at: string,
     *   trigger_type: string,
     *   remarque_globale: ?string,
     *   date_echeance: ?string,
     *   taches: ?array<int, array{tache_id: int, statut: ?string, value_measured: ?float, remarque: ?string}>
     * } $data
     */
    public static function createExec(int $controleId, int $articleId, array $data, int $sapeurId): ControleExec
    {
        $article = Article::findOrFail($articleId);
        $controle = Controle::with(['taches', 'materielTypes'])->findOrFail($controleId);

        self::validateArticleRattacheAuControle($controle, $article);
        self::validateTriggerType($data['trigger_type'], $controle->recurrence_type);

        $tachesById = $controle->taches->keyBy('id');
        $tachesData = $data['taches'] ?? [];

        self::validateAllTachesPresent($tachesById, $tachesData);

        $dateEcheance = self::resolveDateEcheance($controle, $data['date_echeance'] ?? null);

        return DB::transaction(function () use ($controleId, $articleId, $data, $sapeurId, $dateEcheance, $tachesById, $tachesData): ControleExec {
            $exec = ControleExec::create([
                'controle_id'      => $controleId,
                'article_id'       => $articleId,
                'executed_at'      => $data['executed_at'],
                'date_echeance'    => $dateEcheance,
                'executed_by'      => $sapeurId,
                'trigger_type'     => $data['trigger_type'],
                'remarque_globale' => $data['remarque_globale'] ?? null,
            ]);

            self::enregistrerResultats($exec, $tachesById, $tachesData);

            return $exec->load(['controle', 'execTaches.tache', 'executeur']);
        });
    }

    /**
     * Soumet une exécution pour plusieurs articles à la fois (même date
     * d'exécution partagée par toutes) — tout ou rien : si un seul article
     * échoue la validation, aucune exécution n'est enregistrée.
     *
     * @param array<int, array{article_id: int, date_echeance: ?string, taches: ?array<int, array{tache_id: int, statut: ?string, value_measured: ?float, remarque: ?string}>}> $executions
     * @return ControleExec[]
     */
    public static function createExecsMultiple(int $controleId, string $executedAt, string $triggerType, ?string $remarqueGlobale, array $executions, int $sapeurId): array
    {
        return DB::transaction(function () use ($controleId, $executedAt, $triggerType, $remarqueGlobale, $executions, $sapeurId): array {
            return array_map(
                fn (array $execution) => self::createExec($controleId, $execution['article_id'], [
                    'executed_at'      => $executedAt,
                    'trigger_type'     => $triggerType,
                    'remarque_globale' => $remarqueGlobale,
                    'date_echeance'    => $execution['date_echeance'] ?? null,
                    'taches'           => $execution['taches'] ?? [],
                ], $sapeurId),
                $executions,
            );
        });
    }

    /**
     * Modifie une exécution existante : date, remarque globale et résultats
     * des tâches. Le contrôle et l'article restent fixes.
     *
     * @param array{
     *   executed_at: string,
     *   remarque_globale: ?string,
     *   date_echeance: ?string,
     *   taches: ?array<int, array{tache_id: int, statut: ?string, value_measured: ?float, remarque: ?string}>
     * } $data
     */
    public static function updateExec(int $id, array $data): ControleExec
    {
        $exec = ControleExec::with('controle.taches')->findOrFail($id);

        $tachesById = $exec->controle->taches->keyBy('id');
        $tachesData = $data['taches'] ?? [];

        self::validateAllTachesPresent($tachesById, $tachesData);

        $dateEcheance = self::resolveDateEcheance($exec->controle, $data['date_echeance'] ?? null);

        return DB::transaction(function () use ($exec, $data, $dateEcheance, $tachesById, $tachesData): ControleExec {
            $exec->update([
                'executed_at'      => $data['executed_at'],
                'date_echeance'    => $dateEcheance,
                'remarque_globale' => $data['remarque_globale'] ?? null,
            ]);

            $exec->execTaches()->delete();
            self::enregistrerResultats($exec, $tachesById, $tachesData);

            return $exec->load(['controle', 'execTaches.tache', 'executeur']);
        });
    }

    /**
     * Échéance du prochain contrôle pour cette exécution : obligatoire pour un
     * contrôle PERIODIQUE (le front la pré-remplit à partir de la récurrence
     * du contrôle — cf. addMonthsIso côté APP — mais laisse l'utilisateur la
     * modifier, ex : un premier service véhicule à 5 ans puis tous les 2 ans),
     * sans objet pour un contrôle NON_PERIODIQUE (suivi par nombre
     * d'exécutions, pas par date), auquel cas toute valeur soumise est
     * ignorée.
     */
    private static function resolveDateEcheance(Controle $controle, ?string $submitted): ?string
    {
        if ($controle->recurrence_type !== 'PERIODIQUE') {
            return null;
        }

        if ($submitted === null) {
            throw new ArrayException([], "La date d'échéance du prochain contrôle est obligatoire pour un contrôle PERIODIQUE");
        }

        return $submitted;
    }

    /**
     * @param Collection<int, ControleTache> $tachesById
     * @param array<int, array{tache_id: int, statut: ?string, value_measured: ?float, remarque: ?string}> $tachesData
     */
    private static function enregistrerResultats(ControleExec $exec, Collection $tachesById, array $tachesData): void
    {
        foreach ($tachesData as $tacheData) {
            /** @var ControleTache $tache */
            $tache = $tachesById[$tacheData['tache_id']];

            [$statut, $valueMeasured, $valueInRange] = self::processTacheResult($tache, $tacheData);

            ControleExecTache::create([
                'controle_exec_id'    => $exec->id,
                'tache_id'            => $tache->id,
                'task_order_snapshot' => $tache->order,
                'statut'              => $statut,
                'value_measured'      => $valueMeasured,
                'value_min_snapshot'  => $tache->type === 'NUMERIC' ? $tache->value_min : null,
                'value_max_snapshot'  => $tache->type === 'NUMERIC' ? $tache->value_max : null,
                'value_in_range'      => $valueInRange,
                'remarque'            => $tacheData['remarque'] ?? null,
            ]);
        }
    }

    /**
     * @param array<int, array{tache_id: int, statut: ?string, value_measured: ?float, remarque: ?string}> $tacheData
     * @return array{0: ?string, 1: ?float, 2: ?bool}
     */
    private static function processTacheResult(ControleTache $tache, array $tacheData): array
    {
        if ($tache->type === 'BOOLEAN') {
            if (!isset($tacheData['statut']) || !in_array($tacheData['statut'], ['OK', 'KO', 'NA'], true)) {
                throw new ArrayException([], "Statut invalide pour la tâche BOOLEAN « {$tache->nom} » (attendu : OK, KO ou NA)");
            }

            return [$tacheData['statut'], null, null];
        }

        // NUMERIC
        if (!isset($tacheData['value_measured'])) {
            throw new ArrayException([], "Valeur mesurée obligatoire pour la tâche NUMERIC « {$tache->nom} »");
        }

        $valueInRange = self::computeInRange(
            (float) $tacheData['value_measured'],
            $tache->value_min !== null ? (float) $tache->value_min : null,
            $tache->value_max !== null ? (float) $tache->value_max : null,
        );

        return [null, (float) $tacheData['value_measured'], $valueInRange];
    }

    private static function computeInRange(float $value, ?float $min, ?float $max): bool
    {
        if ($min === null && $max === null) {
            return true;
        }
        if ($min !== null && $max !== null) {
            return $value >= $min && $value <= $max;
        }
        if ($min !== null) {
            return $value >= $min;
        }
        return $value <= $max;
    }

    /**
     * @param Collection<int, ControleTache> $tachesById
     * @param array<int, array{tache_id: int}> $tachesData
     */
    private static function validateAllTachesPresent(Collection $tachesById, array $tachesData): void
    {
        $expectedIds = $tachesById->keys()->sort()->values();
        $providedIds = collect($tachesData)->pluck('tache_id')->sort()->values();

        if ($expectedIds->toArray() !== $providedIds->toArray()) {
            throw new ArrayException([], "Toutes les tâches du contrôle doivent être renseignées avant soumission");
        }
    }

    public static function deleteExec(int $id): bool
    {
        return (bool) ControleExec::whereId($id)->delete();
    }

    /**
     * Pour chaque article ayant déjà été contrôlé pour ce contrôle : date et
     * nombre d'exécutions, échéance enregistrée sur la dernière exécution
     * (contrôle PERIODIQUE uniquement) et statut qui en découle — même calcul
     * que ControleBusiness::statutArticlePourControle, réutilisé ici pour ne
     * pas le réimplémenter — plus si l'article doit être considéré en échec :
     * dernière exécution non conforme (tâche KO ou valeur hors plage) OU
     * nombre d'exécutions maximum atteint — ce dernier cas s'applique même à
     * un contrôle sans tâche.
     *
     * Un article jamais contrôlé n'apparaît pas dans le résultat : à la charge
     * de l'appelant, qui connaît l'ensemble des articles éligibles au
     * contrôle, de lui attribuer le statut par défaut ("warning" pour un
     * contrôle PERIODIQUE, sinon aucun).
     *
     * @return Collection<int, object{article_id: int, derniere_execution: string, date_echeance: ?string, nb_executions: int, statut: ?string, dernier_controle_echec: bool}>
     */
    public static function getDernieresExecutionsParArticle(int $controleId): Collection
    {
        $controle = Controle::findOrFail($controleId);

        $agregats = ControleExec::where('controle_id', $controleId)
            ->get(['article_id', 'executed_at', 'date_echeance'])
            ->groupBy('article_id')
            ->map(function (Collection $execs) {
                $derniere = $execs->sortByDesc('executed_at')->first();

                return (object) [
                    'article_id'         => $derniere->article_id,
                    'derniere_execution' => $derniere->executed_at->toDateTimeString(),
                    'date_echeance'      => $derniere->date_echeance?->toDateString(),
                    'nb_executions'      => $execs->count(),
                ];
            });

        $dernierExecParArticle = ControleExec::where('controle_id', $controleId)
            ->with('execTaches')
            ->get()
            ->groupBy('article_id')
            ->map(fn (Collection $execs) => $execs->sortByDesc('executed_at')->first());

        $maintenant = Carbon::now();

        return $agregats->map(function ($ligne) use ($dernierExecParArticle, $controle, $maintenant) {
            $dernierExec = $dernierExecParArticle->get($ligne->article_id);
            $echecTache = $dernierExec !== null && !$dernierExec->isConforme();
            $nbExecutionMaxAtteint = $controle->nb_execution_max !== null
                && $ligne->nb_executions >= $controle->nb_execution_max;

            $ligne->statut = ControleBusiness::statutArticlePourControle($controle, $ligne, $maintenant);
            $ligne->dernier_controle_echec = $echecTache || $nbExecutionMaxAtteint;

            return $ligne;
        });
    }

    /**
     * Un contrôle ne s'exécute que sur un article dont le type de matériel lui
     * est rattaché : sans ce garde-fou, une exécution enregistrée sur un article
     * arbitraire apparaîtrait dans son historique sans jamais être suivie par le
     * tableau de bord.
     */
    private static function validateArticleRattacheAuControle(Controle $controle, Article $article): void
    {
        $typeIds = $controle->materielTypes->pluck('materiel_type_id');

        if (!$typeIds->contains($article->materiel_type_id)) {
            throw new ArrayException([], "Le contrôle « {$controle->nom} » ne s'applique pas au type de matériel de cet article");
        }
    }

    private static function validateTriggerType(string $triggerType, string $recurrenceType): void
    {
        $allowed = match ($recurrenceType) {
            'PERIODIQUE'     => ['PERIODIQUE'],
            'NON_PERIODIQUE' => ['NON_PERIODIQUE'],
            default          => [],
        };

        if (!in_array($triggerType, $allowed, true)) {
            throw new ArrayException([], "Le trigger_type « {$triggerType} » ne correspond pas au type de récurrence du contrôle");
        }
    }
}
