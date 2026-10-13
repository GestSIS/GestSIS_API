<?php

namespace App\Console\Commands;

use App\Support\Sis;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seul un type numéroté peut être contrôlé : liste, pour chaque SIS, les
 * contrôles dont des articles ne sont pas identifiables par leur numéro, soit
 * parce que leur type n'est pas numéroté (le contrôle ne pourra plus être
 * modifié tant que ce type y est rattaché), soit parce qu'ils n'ont pas de
 * numéro alors que leur type est numéroté. Lecture seule.
 */
class DbsControlesNonNumerotes extends Command
{
    protected $signature = 'dbs:controles-non-numerotes {--sis=* : Limiter à ces SIS}';

    protected $description = 'Liste par SIS les contrôles ayant des articles non numérotés';

    public function handle(): int
    {
        $sisDemandes = $this->option('sis');
        foreach ($sisDemandes as $sis) {
            if (!Sis::isValid($sis)) {
                $this->error("SIS inconnu : {$sis}");

                return self::FAILURE;
            }
        }

        Sis::each(function (string $sis): void {
            $lignes = $this->lignesAProbleme();

            if ($lignes === []) {
                $this->info("SIS {$sis} : aucun contrôle concerné");

                return;
            }

            $this->warn("SIS {$sis} :");
            $this->table(['Contrôle', 'Type de matériel', 'Problème', 'Articles concernés'], $lignes);
        }, $sisDemandes === [] ? null : $sisDemandes);

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: int}>
     */
    private function lignesAProbleme(): array
    {
        return DB::table('controle_materiel_types')
            ->join('controles', 'controles.id', '=', 'controle_materiel_types.controle_id')
            ->join('materiel_types', 'materiel_types.id', '=', 'controle_materiel_types.materiel_type_id')
            ->orderBy('controles.nom')
            ->orderBy('materiel_types.designation')
            ->get([
                'controles.nom as controle',
                'materiel_types.id as type_id',
                'materiel_types.designation as type',
                'materiel_types.est_numerote',
            ])
            ->map(function (object $ligne): ?array {
                $articles = DB::table('articles')->where('materiel_type_id', $ligne->type_id);

                if (!$ligne->est_numerote) {
                    return [$ligne->controle, $ligne->type, 'type non numéroté', $articles->count()];
                }

                $sansNumero = $articles->whereRaw("TRIM(numero) = ''")->count();

                return $sansNumero > 0
                    ? [$ligne->controle, $ligne->type, 'articles sans numéro', $sansNumero]
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
