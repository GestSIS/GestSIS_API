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
     *   taches: array<int, array{id?: int, order: int, nom: string, description: ?string, type: string, unit: ?string, value_min: ?float, value_max: ?float}>,
     *   materiel_type_ids: int[]
     * } $data
     */
    public static function createControle(array $data): Controle
    {
        self::validateRecurrence($data['recurrence_type'], $data['recurrence_value'] ?? null);
        self::validateDureePreavis($data['recurrence_type'], $data['duree_preavis'] ?? null);

        return DB::transaction(function () use ($data): Controle {
            $controle = Controle::create([
                'nom'              => $data['nom'],
                'description'      => $data['description'] ?? null,
                'recurrence_type'  => $data['recurrence_type'],
                'recurrence_value' => $data['recurrence_value'] ?? null,
                'duree_preavis'    => $data['duree_preavis'] ?? null,
                'externe'          => $data['externe'] ?? false,
                'reparateur'       => $data['reparateur'] ?? null,
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
     *   taches: array<int, array{id?: int, order: int, nom: string, description: ?string, type: string, unit: ?string, value_min: ?float, value_max: ?float}>,
     *   materiel_type_ids: int[]
     * } $data
     */
    public static function editControle(int $id, array $data): Controle
    {
        self::validateRecurrence($data['recurrence_type'], $data['recurrence_value'] ?? null);
        self::validateDureePreavis($data['recurrence_type'], $data['duree_preavis'] ?? null);

        return DB::transaction(function () use ($id, $data): Controle {
            $controle = Controle::findOrFail($id);

            $controle->update([
                'nom'              => $data['nom'],
                'description'      => $data['description'] ?? null,
                'recurrence_type'  => $data['recurrence_type'],
                'recurrence_value' => $data['recurrence_value'] ?? null,
                'duree_preavis'    => $data['duree_preavis'] ?? null,
                'externe'          => $data['externe'] ?? false,
                'reparateur'       => $data['reparateur'] ?? null,
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

            if (!empty($tacheData['id'])) {
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

        foreach ($typeIds as $typeId) {
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
            'NON_PERIODIQUE', 'APRES_USAGE' => $value !== null
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

    /**
     * Pour chaque contrôle PERIODIQUE, compte les articles actifs en retard
     * (échéance dépassée) et en préavis (échéance proche, ou jamais contrôlés).
     * Calculé à la demande en quelques requêtes agrégées (pas de requête par article).
     *
     * @return array<int, array{controle_id: int, nb_en_retard: int, nb_en_preavis: int}>
     */
    public static function calculerStatuts(): array
    {
        $controles = Controle::where('recurrence_type', 'PERIODIQUE')->with('materielTypes')->get();

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

        $dernieresExecutionsParControle = ControleExec::whereIn('controle_id', $controles->pluck('id'))
            ->selectRaw('controle_id, article_id, MAX(executed_at) as derniere_execution')
            ->groupBy('controle_id', 'article_id')
            ->get()
            ->groupBy('controle_id');

        $maintenant = Carbon::now();

        return $controles->map(function (Controle $controle) use ($articlesParType, $dernieresExecutionsParControle, $maintenant): array {
            $articleIds = $controle->materielTypes
                ->flatMap(fn (ControleMaterielType $mt) => $articlesParType->get($mt->materiel_type_id, collect())->pluck('id'))
                ->unique();

            $execsControle = ($dernieresExecutionsParControle->get($controle->id) ?? collect())->keyBy('article_id');

            $nbEnRetard = 0;
            $nbEnPreavis = 0;

            foreach ($articleIds as $articleId) {
                $derniereExecution = $execsControle->get($articleId)?->derniere_execution;

                if ($derniereExecution === null) {
                    $nbEnPreavis++;
                    continue;
                }

                $prochaine = Carbon::parse($derniereExecution)->addMonths($controle->recurrence_value);

                if ($maintenant->greaterThanOrEqualTo($prochaine)) {
                    $nbEnRetard++;
                } elseif (
                    $controle->duree_preavis !== null &&
                    $maintenant->greaterThanOrEqualTo($prochaine->copy()->subMonths($controle->duree_preavis))
                ) {
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
}
