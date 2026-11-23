<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehiculeTableSeeder extends Seeder
{
    /**
     * Un véhicule (ou une remorque) est un article dont le type est un emplacement : il n'est
     * rangé nulle part lui-même, sa position est le parent_id de l'emplacement qu'il représente.
     *
     * Crée un article par véhicule, dans l'ordre de la liste (articles 1, 2, 3… : tonne-pompe,
     * véhicule PR, véhicule hydrocarbures…), et le relie à son emplacement.
     *
     * @var array<int, string> id de l'emplacement (EmplacementTableSeeder) => type du véhicule
     */
    private const VEHICULES = [
        6 => 'Fourgon Tonne-Pompe',
        7 => 'Véhicule Protection Respiratoire',
        8 => 'Véhicule Hydrocarbures',
        9 => 'Remorque Tonne-Pompe',
        10 => 'Echelle Remorquable',
        11 => 'Remorque motopompe type 1',
        12 => 'Remorque hydrocarbures',
        13 => 'Remorque inondation',
        14 => 'Remorque tuyaux',
        15 => 'Remorque mousse',
        16 => 'Remorque de transport',
        18 => 'Véhicule Première Intervention',
        19 => 'Remorque échelles',
        20 => 'Remorque motopompe type 2',
        21 => 'Remorque inondation',
        23 => 'Véhicule Première Intervention',
        24 => 'Remorque échelles',
        25 => 'Remorque motopompe type 2',
        26 => 'Etagère inondation',
        28 => 'Remorque Première Intervention',
        29 => 'Remorque échelles',
        30 => 'Remorque motopompe type 1',
        31 => 'Remorque inondation',
    ];

    public function run(): void
    {
        $articleId = 0;
        foreach (self::VEHICULES as $emplacementId => $typeDesignation) {
            $articleId++;
            DB::table('articles')->insert([
                'id' => $articleId,
                'materiel_type_id' => DB::table('materiel_types')
                    ->where('designation', $typeDesignation)
                    ->where('est_emplacement', true)
                    ->soleValue('id'),
                'numero' => "JU {$articleId}",
                'designation' => DB::table('emplacements')->where('id', $emplacementId)->value('designation'),
                'emplacement_id' => null,
                'uuid' => (string) Str::uuid(),
                'created_at' => '2025-01-01',
            ]);

            DB::table('emplacements')->where('id', $emplacementId)->update(['article_id' => $articleId]);
        }
    }
}
