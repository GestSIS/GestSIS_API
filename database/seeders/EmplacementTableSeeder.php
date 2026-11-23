<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmplacementTableSeeder extends Seeder
{
    /**
     * SIS fictif de démo : quatre casernes (aussi des hangars, cf. HangarTableSeeder) et un stock,
     * avec leurs véhicules, remorques et hangars. La structure s'inspire d'un vrai SIS, mais les
     * noms sont inventés. Ids fixes, utilisés par VehiculeTableSeeder et ArticleTableSeeder.
     *
     * @var list<array{0: int, 1: ?int, 2: string, 3: int}> id, parent, désignation, couleur
     */
    private const EMPLACEMENTS = [
        [1, null, 'Valcourt', 1],
        [2, null, 'Montfleur', 2],
        [3, null, 'Rochebois', 3],
        [4, null, 'Belprés', 2],
        [5, null, 'Stock', 9],
        [6, 1, 'Tonne-pompe', 4],
        [7, 1, 'Véhicule PR', 5],
        [8, 1, 'Véhicule hydrocarbures', 7],
        [9, 1, 'Remorque tonne-pompe', 4],
        [10, 1, 'Échelle remorquable', 1],
        [11, 1, 'Remorque MP T1', 1],
        [12, 1, 'Remorque hydrocarbures', 7],
        [13, 1, 'Remorque inondation', 7],
        [14, 1, 'Remorque tuyaux', 1],
        [15, 1, 'Remorque mousse', 1],
        [16, 1, 'Remorque transport', 1],
        [17, 1, 'Hangar', 1],
        [18, 2, 'VPI', 4],
        [19, 2, 'Remorque échelles', 2],
        [20, 2, 'Remorque MP T2', 6],
        [21, 2, 'Remorque inondation', 7],
        [22, 2, 'Hangar', 2],
        [23, 3, 'VPI', 4],
        [24, 3, 'Remorque échelles', 3],
        [25, 3, 'Remorque MP T2', 6],
        [26, 3, 'Étagère inondation', 7],
        [27, 3, 'Hangar', 3],
        [28, 4, 'RPI', 5],
        [29, 4, 'Remorque échelles', 2],
        [30, 4, 'Remorque MP T1', 2],
        [31, 4, 'Remorque inondation', 7],
        [32, 4, 'Hangar', 2],
    ];

    private const COFFRES_FOURGON = ['Cabine', 'G1 · Gauche avant', 'G2 · Gauche arrière', 'D1 · Droite avant', 'D2 · Droite arrière', 'Toit'];

    /**
     * Sous-emplacements des véhicules, de la couleur de leur véhicule.
     *
     * @var array<int, list<string>> id du véhicule => sous-emplacements
     */
    private const SOUS_EMPLACEMENTS = [
        6 => self::COFFRES_FOURGON,                         // Tonne-pompe
        7 => ['Cabine', 'Coffre gauche', 'Coffre droit'],   // Véhicule PR
        8 => ['Cabine', 'Coffre gauche', 'Coffre droit'],   // Véhicule hydrocarbures
        18 => self::COFFRES_FOURGON,                        // VPI Montfleur
        23 => self::COFFRES_FOURGON,                        // VPI Rochebois
        28 => ['Coffre gauche', 'Coffre droit'],            // RPI
    ];

    public function run(): void
    {
        $triParParent = [];
        $row = function (?int $parentId, string $designation, int $couleurId) use (&$triParParent): array {
            $parentKey = $parentId ?? 0;
            $triParParent[$parentKey] = ($triParParent[$parentKey] ?? 0) + 1;

            return [
                'parent_id' => $parentId,
                'designation' => $designation,
                'est_etiquete' => true,
                'est_compartimentable' => false,
                'tri' => $triParParent[$parentKey],
                'couleur_id' => $couleurId,
                'statut' => true,
            ];
        };

        $couleurParId = [];
        $emplacements = [];
        foreach (self::EMPLACEMENTS as [$id, $parentId, $designation, $couleurId]) {
            $couleurParId[$id] = $couleurId;
            $emplacements[] = ['id' => $id, ...$row($parentId, $designation, $couleurId)];
        }
        DB::table('emplacements')->insert($emplacements);

        $sousEmplacements = [];
        foreach (self::SOUS_EMPLACEMENTS as $vehiculeId => $designations) {
            foreach ($designations as $designation) {
                $sousEmplacements[] = $row($vehiculeId, $designation, $couleurParId[$vehiculeId]);
            }
        }
        DB::table('emplacements')->insert($sousEmplacements);
    }
}
