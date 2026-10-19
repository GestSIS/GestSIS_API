<?php

namespace App\Console\Commands;

use App\Domaine\Business\SapeurBusiness;
use App\Support\Sis;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pour chaque SIS et au global : nombre de sapeurs par genre (civilité) et âge
 * moyen d'incorporation par genre. L'âge d'incorporation est calculé à la
 * première mutation du sapeur ; les sapeurs sans mutation ou dont la date
 * d'incorporation précède la date de naissance sont exclus de la moyenne.
 * La moyenne globale est pondérée par le nombre de sapeurs.
 * Sont exclus : les SIS dont le nom contient « jsp » ou « test », et les
 * sapeurs ayant (ou ayant eu) un grade de jeunes sapeurs-pompiers. Lecture seule.
 */
class DbsStatistiquesGenre extends Command
{
    protected $signature = 'dbs:statistiques-genre
                            {--sis=* : Limiter à ces SIS}
                            {--tous : Inclure les sapeurs inactifs (par défaut, seuls les actifs sont comptés)}';

    protected $description = 'Statistiques par SIS et globales du nombre de sapeurs par genre et de leur âge moyen d\'incorporation';

    /**
     * @var array<int, string>
     */
    private const GENRES = [
        1 => 'Homme',
        2 => 'Femme',
        3 => 'Non-binaire',
    ];

    /**
     * @var array<int, string>
     */
    private const SIS_EXCLUS = ['jsp', 'test'];

    public function handle(): int
    {
        $sisDemandes = $this->option('sis');
        foreach ($sisDemandes as $sis) {
            if (!Sis::isValid($sis)) {
                $this->error("SIS inconnu : {$sis}");

                return self::FAILURE;
            }
        }

        $sisRetenus = array_values(array_filter(
            $sisDemandes === [] ? Sis::keys() : $sisDemandes,
            fn (string $sis): bool => !Str::contains($sis, self::SIS_EXCLUS, ignoreCase: true)
        ));
        if ($sisRetenus === []) {
            $this->warn('Aucun SIS à analyser (les SIS contenant « jsp » ou « test » sont exclus)');

            return self::SUCCESS;
        }

        $inclureInactifs = (bool) $this->option('tous');
        $statistiquesParSis = Sis::each(
            fn (): array => $this->statistiques($inclureInactifs),
            $sisRetenus
        );

        $lignes = [];
        foreach ($statistiquesParSis as $sis => $statistiques) {
            $lignes[] = $this->ligne($sis, $statistiques);
        }
        $lignes[] = $this->ligne('GLOBAL', $this->cumul($statistiquesParSis));

        $this->info($inclureInactifs ? 'Population : tous les sapeurs' : 'Population : sapeurs actifs');
        $this->table($this->entetes(), $lignes);

        return self::SUCCESS;
    }

    /**
     * Statistiques par genre pour le SIS courant.
     *
     * @return array<int, array{nombre: int, nombre_avec_age: int, somme_ages: float}>
     */
    private function statistiques(bool $inclureInactifs): array
    {
        $resultats = DB::select("SELECT s.civilite_id,
                    count(*) as nombre,
                    count(age.age) as nombre_avec_age,
                    coalesce(sum(age.age), 0) as somme_ages
                FROM sapeurs as s
                LEFT JOIN (
                    SELECT s2.id as sapeur_id, DATEDIFF(min(m.incorporation), s2.date_naissance) / 365.25 as age
                    FROM sapeurs as s2
                    INNER JOIN mutations as m ON m.sapeur_id = s2.id
                    GROUP BY s2.id, s2.date_naissance
                    HAVING min(m.incorporation) > s2.date_naissance
                ) as age ON age.sapeur_id = s.id
                WHERE s.type = ?
                AND (? OR s.actif = 1)
                AND NOT EXISTS (
                    SELECT 1
                    FROM grades as g
                    LEFT JOIN grade_sapeur as gs ON gs.grade_id = g.id AND gs.sapeur_id = s.id
                    WHERE (g.designation LIKE '%jeune%sapeur%pompier%' OR g.abreviation = 'JSP')
                    AND (gs.id IS NOT NULL OR g.id = s.grade_id)
                )
                GROUP BY s.civilite_id
            ", [SapeurBusiness::TYPE_SAPEUR, $inclureInactifs]);

        $statistiques = array_map(fn (): array => $this->statistiquesVides(), self::GENRES);
        foreach ($resultats as $resultat) {
            if (!isset($statistiques[$resultat->civilite_id])) {
                continue;
            }
            $statistiques[$resultat->civilite_id] = [
                'nombre' => (int) $resultat->nombre,
                'nombre_avec_age' => (int) $resultat->nombre_avec_age,
                'somme_ages' => (float) $resultat->somme_ages,
            ];
        }

        return $statistiques;
    }

    /**
     * @param array<string, array<int, array{nombre: int, nombre_avec_age: int, somme_ages: float}>> $statistiquesParSis
     * @return array<int, array{nombre: int, nombre_avec_age: int, somme_ages: float}>
     */
    private function cumul(array $statistiquesParSis): array
    {
        $cumul = array_map(fn (): array => $this->statistiquesVides(), self::GENRES);
        foreach ($statistiquesParSis as $statistiques) {
            foreach ($statistiques as $civiliteId => $genre) {
                $cumul[$civiliteId]['nombre'] += $genre['nombre'];
                $cumul[$civiliteId]['nombre_avec_age'] += $genre['nombre_avec_age'];
                $cumul[$civiliteId]['somme_ages'] += $genre['somme_ages'];
            }
        }

        return $cumul;
    }

    /**
     * @return array{nombre: int, nombre_avec_age: int, somme_ages: float}
     */
    private function statistiquesVides(): array
    {
        return ['nombre' => 0, 'nombre_avec_age' => 0, 'somme_ages' => 0.0];
    }

    /**
     * @return array<int, string>
     */
    private function entetes(): array
    {
        return [
            'SIS',
            ...array_map(fn (string $genre): string => "Nb {$genre}", self::GENRES),
            'Nb total',
            ...array_map(fn (string $genre): string => "Âge moy. {$genre}", self::GENRES),
            'Âge moy. total',
        ];
    }

    /**
     * @param array<int, array{nombre: int, nombre_avec_age: int, somme_ages: float}> $statistiques
     * @return array<int, string|int>
     */
    private function ligne(string $libelle, array $statistiques): array
    {
        $total = array_sum(array_column($statistiques, 'nombre'));

        return [
            $libelle,
            ...array_values(array_map(
                fn (array $genre): string => $this->nombreEtPourcentage($genre['nombre'], $total),
                $statistiques
            )),
            $total,
            ...array_values(array_map(
                fn (array $genre): string => $this->moyenne($genre['somme_ages'], $genre['nombre_avec_age']),
                $statistiques
            )),
            $this->moyenne(
                array_sum(array_column($statistiques, 'somme_ages')),
                array_sum(array_column($statistiques, 'nombre_avec_age'))
            ),
        ];
    }

    private function nombreEtPourcentage(int $nombre, int $total): string
    {
        $pourcentage = $total > 0 ? number_format($nombre / $total * 100, 1, '.', '') : '0.0';

        return "{$nombre} ({$pourcentage} %)";
    }

    private function moyenne(float $somme, int $nombre): string
    {
        return $nombre > 0 ? number_format($somme / $nombre, 1, '.', '') : '-';
    }
}
