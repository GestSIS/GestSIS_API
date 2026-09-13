<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleTache;
use App\Models\ControleExec;
use App\Models\ControleExecTache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ControleExecBusiness
{
    /**
     * Soumet une exécution complète (toutes les tâches doivent être renseignées).
     *
     * @param array{
     *   executed_at: string,
     *   trigger_type: string,
     *   remarque_globale: ?string,
     *   taches: array<int, array{tache_id: int, statut: ?string, value_measured: ?float, remarque: ?string}>
     * } $data
     */
    public static function createExec(int $controleId, int $articleId, array $data, int $sapeurId): ControleExec
    {
        Article::findOrFail($articleId);
        $controle = Controle::with('taches')->findOrFail($controleId);

        self::validateTriggerType($data['trigger_type'], $controle->recurrence_type);

        $tachesById = $controle->taches->keyBy('id');

        self::validateAllTachesPresent($tachesById, $data['taches']);

        return DB::transaction(function () use ($controleId, $articleId, $data, $sapeurId, $tachesById): ControleExec {
            $exec = ControleExec::create([
                'controle_id'      => $controleId,
                'article_id'       => $articleId,
                'executed_at'      => $data['executed_at'],
                'executed_by'      => $sapeurId,
                'trigger_type'     => $data['trigger_type'],
                'remarque_globale' => $data['remarque_globale'] ?? null,
            ]);

            foreach ($data['taches'] as $tacheData) {
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

            return $exec->load(['controle', 'execTaches.tache', 'executeur']);
        });
    }

    /**
     * @param array<int, array{tache_id: int, statut: ?string, value_measured: ?float, remarque: ?string}> $tacheData
     * @return array{0: ?string, 1: ?float, 2: ?bool}
     */
    private static function processTacheResult(ControleTache $tache, array $tacheData): array
    {
        if ($tache->type === 'BOOLEAN') {
            if (!isset($tacheData['statut']) || !in_array($tacheData['statut'], ['OK', 'NOK', 'NA'], true)) {
                throw new ArrayException([], "Statut invalide pour la tâche BOOLEAN « {$tache->nom} » (attendu : OK, NOK ou NA)");
            }
            if ($tacheData['statut'] === 'NOK' && ($tacheData['remarque'] ?? '') === '') {
                throw new ArrayException([], "Remarque obligatoire pour la tâche « {$tache->nom} » avec statut NOK");
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

        if (!$valueInRange && ($tacheData['remarque'] ?? '') === '') {
            throw new ArrayException([], "Remarque obligatoire pour la tâche « {$tache->nom} » dont la valeur est hors plage");
        }

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

    private static function validateTriggerType(string $triggerType, string $recurrenceType): void
    {
        $allowed = match ($recurrenceType) {
            'PERIODIQUE'     => ['PERIODIQUE'],
            'NON_PERIODIQUE' => ['NON_PERIODIQUE'],
            'APRES_USAGE'    => ['APRES_USAGE'],
            default          => [],
        };

        if (!in_array($triggerType, $allowed, true)) {
            throw new ArrayException([], "Le trigger_type « {$triggerType} » ne correspond pas au type de récurrence du contrôle");
        }
    }
}
