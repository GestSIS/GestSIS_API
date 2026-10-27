<?php

namespace App\Console\Commands;

use App\Support\ConversionHeuresExercice;
use App\Support\Sis;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convertit, pour chaque SIS, les heures supplémentaires d'exercice en fiches de travail :
 * les types d'heures deviennent des types de fiches liés obligatoirement à un exercice, les
 * heures saisies des fiches, et les écritures déjà imputées sont rattachées à ces fiches.
 * Les lignes converties sont supprimées, ce qui permet de relancer la commande sans effet.
 * Avec --dry-run, tout est annulé en fin de SIS : seul le rapport est affiché.
 */
class DbsMigrerHeuresExercice extends Command
{
    protected $signature = 'dbs:migrer-heures-exercice
        {--sis=* : Limiter à ces SIS}
        {--dry-run : Simuler la conversion et afficher le rapport, sans rien modifier}';

    protected $description = 'Convertit les heures supplémentaires d\'exercice en fiches de travail';

    private const MODULE_EXERCICE = 1;
    private const EXERCICE_IMPUTE = 4;

    public function handle(): int
    {
        $sisDemandes = $this->option('sis');
        foreach ($sisDemandes as $sis) {
            if (!Sis::isValid($sis)) {
                $this->error("SIS inconnu : {$sis}");

                return self::FAILURE;
            }
        }

        $simulation = (bool) $this->option('dry-run');

        Sis::each(function (string $sis) use ($simulation): void {
            if (!Schema::hasTable('heure_exercice_types')) {
                $this->info("SIS {$sis} : rien à convertir");

                return;
            }

            DB::beginTransaction();
            try {
                $rapport = $this->convertir();
                $simulation ? DB::rollBack() : DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            $this->afficher($sis, $rapport, $simulation);
        }, $sisDemandes === [] ? null : $sisDemandes);

        return self::SUCCESS;
    }

    /**
     * @return array{types: int, heures: int, rattachees: int, heuresSansEcriture: array<int, string>, ecrituresNonReliees: array<int, string>}
     */
    private function convertir(): array
    {
        $maintenant = Carbon::now();
        $designations = DB::table('travail_types')->pluck('designation')->all();

        // Types d'heures → types de fiches, en gardant la correspondance des id
        $types = [];
        foreach (DB::table('heure_exercice_types')->orderBy('id')->get() as $heureType) {
            $types[$heureType->id] = $this->creerType((array) $heureType, $designations, $maintenant);
        }

        $heures = DB::table('heure_exercices')
            ->join('exercices', 'exercices.id', '=', 'heure_exercices.exercice_id')
            ->orderBy('heure_exercices.id')
            ->get([
                'heure_exercices.*',
                'exercices.date AS exercice_date',
                'exercices.statut AS exercice_statut',
                'exercices.exercice_comptable_id AS exercice_comptable_id',
            ]);

        $typesOrphelins = [];
        $convertis = [];
        $utilisees = [];
        $designationsParExercice = [];
        $heuresSansEcriture = [];
        foreach ($heures as $ligne) {
            $heure = (array) $ligne;

            // Heure dont le type n'existe plus : un type créé depuis sa copie du tarif
            if (isset($types[$heure['heure_exercice_type_id']])) {
                $type = $types[$heure['heure_exercice_type_id']];
            } else {
                $type = $typesOrphelins[$heure['designation']] ??= $this->creerType($heure, $designations, $maintenant);
            }

            $exercice = [
                'id' => $heure['exercice_id'],
                'date' => $heure['exercice_date'],
                'statut' => $heure['exercice_statut'],
                'exercice_comptable_id' => $heure['exercice_comptable_id'],
            ];
            $travailId = DB::table('travaux')->insertGetId([
                ...ConversionHeuresExercice::travailDepuisHeure($heure, $exercice, $type['id']),
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ]);
            $convertis[] = $heure['id'];

            if ((int) $exercice['statut'] !== self::EXERCICE_IMPUTE) {
                continue;
            }

            $designationsParExercice[$heure['exercice_id']][] = $heure['designation'];
            $ecriture = DB::table('ecritures')
                ->where('exercice_id', $heure['exercice_id'])
                ->where('sapeur_id', $heure['sapeur_id'])
                ->where('module', self::MODULE_EXERCICE)
                ->whereNull('travail_id')
                ->whereNotIn('id', $utilisees)
                ->orderBy('id')
                ->get()
                ->first(fn($e) => ConversionHeuresExercice::ecritureCorrespond((array) $e, $heure));

            if ($ecriture === null) {
                $heuresSansEcriture[] = "exercice {$heure['exercice_id']}, sapeur {$heure['sapeur_id']}, {$heure['designation']} ({$heure['quantite']})";
                continue;
            }

            DB::table('ecritures')->where('id', $ecriture->id)->update([
                ...ConversionHeuresExercice::ecritureAlignee((array) $ecriture, $heure, $type['designation'], $travailId),
                'updated_at' => $maintenant,
            ]);
            $utilisees[] = $ecriture->id;
        }

        $ecrituresNonReliees = $this->ecrituresNonReliees($designationsParExercice);

        // Les lignes converties disparaissent : une deuxième exécution ne convertit rien
        DB::table('heure_exercices')->whereIn('id', $convertis)->delete();
        DB::table('heure_exercice_types')
            ->whereNotIn('id', DB::table('heure_exercices')->whereNotNull('heure_exercice_type_id')->select('heure_exercice_type_id'))
            ->delete();

        return [
            'types' => count($types) + count($typesOrphelins),
            'heures' => count($convertis),
            'rattachees' => count($utilisees),
            'heuresSansEcriture' => $heuresSansEcriture,
            'ecrituresNonReliees' => $ecrituresNonReliees,
        ];
    }

    /**
     * @param array<string, mixed> $source type d'heure, ou heure dont le type n'existe plus
     * @param array<int, string> $designations désignations déjà prises, complétées au fur et à mesure
     * @return array{id: int, designation: string}
     */
    private function creerType(array $source, array &$designations, Carbon $maintenant): array
    {
        $designation = ConversionHeuresExercice::designationLibre($source['designation'], $designations);
        $designations[] = $designation;
        $conversion = ConversionHeuresExercice::typeDepuisHeureType($source, $designation);

        $typeId = DB::table('travail_types')->insertGetId([
            ...$conversion['type'],
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);
        DB::table('travail_type_fonctions')->insert([
            ...$conversion['ligne'],
            'travail_type_id' => $typeId,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);

        return ['id' => $typeId, 'designation' => $designation];
    }

    /**
     * Écritures d'heures sup restées sans fiche : module exercice, non rattachées, et dont la
     * désignation se termine par celle d'une heure de leur exercice.
     *
     * @param array<int, array<int, string>> $designationsParExercice
     * @return array<int, string>
     */
    private function ecrituresNonReliees(array $designationsParExercice): array
    {
        $lignes = [];
        foreach ($designationsParExercice as $exerciceId => $designationsHeures) {
            $ecritures = DB::table('ecritures')
                ->where('exercice_id', $exerciceId)
                ->where('module', self::MODULE_EXERCICE)
                ->whereNull('travail_id')
                ->orderBy('id')
                ->get();
            foreach ($ecritures as $ecriture) {
                foreach (array_unique($designationsHeures) as $designation) {
                    if (str_ends_with($ecriture->designation, ' - ' . $designation)) {
                        $lignes[] = "écriture {$ecriture->id} : exercice {$exerciceId}, sapeur {$ecriture->sapeur_id}, {$ecriture->designation} ({$ecriture->quantite})";
                        break;
                    }
                }
            }
        }

        return $lignes;
    }

    /**
     * @param array{types: int, heures: int, rattachees: int, heuresSansEcriture: array<int, string>, ecrituresNonReliees: array<int, string>} $rapport
     */
    private function afficher(string $sis, array $rapport, bool $simulation): void
    {
        $prefixe = $simulation ? '[simulation] ' : '';
        $this->info("{$prefixe}SIS {$sis} : {$rapport['types']} types et {$rapport['heures']} heures convertis, {$rapport['rattachees']} écritures rattachées");

        if ($rapport['heuresSansEcriture'] !== []) {
            $this->warn("{$prefixe}SIS {$sis} : heures d'exercices imputés sans écriture correspondante :");
            foreach ($rapport['heuresSansEcriture'] as $ligne) {
                $this->line("  - {$ligne}");
            }
        }

        if ($rapport['ecrituresNonReliees'] !== []) {
            $this->warn("{$prefixe}SIS {$sis} : écritures d'heures supplémentaires non reliées à une fiche :");
            foreach ($rapport['ecrituresNonReliees'] as $ligne) {
                $this->line("  - {$ligne}");
            }
        }
    }
}
