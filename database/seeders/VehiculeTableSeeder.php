<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Un véhicule est aussi un emplacement (est_emplacement) : il ne se range plus
        // lui-même dans un emplacement, sa position est portée par le parent_id de
        // l'emplacement qu'il représente (cf. ci-dessous).
        DB::table('articles')->insert([
            ['id' => 1, 'materiel_type_id' => 18, 'designation' => 'Tonne-Pompe', 'emplacement_id' => null, 'uuid' => uniqid()],
            ['id' => 2, 'materiel_type_id' => 19, 'designation' => 'VPM', 'emplacement_id' => null, 'uuid' => uniqid()],
            ['id' => 3, 'materiel_type_id' => 20, 'designation' => 'Iveco', 'emplacement_id' => null, 'uuid' => uniqid()],
            ['id' => 4, 'materiel_type_id' => 20, 'designation' => 'Mowag', 'emplacement_id' => null, 'uuid' => uniqid()],
            ['id' => 5, 'materiel_type_id' => 21, 'designation' => 'VPI (Glovelier)', 'emplacement_id' => null, 'uuid' => uniqid()],
            ['id' => 6, 'materiel_type_id' => 20, 'designation' => 'Jeep', 'emplacement_id' => null, 'uuid' => uniqid()],
            ['id' => 7, 'materiel_type_id' => 21, 'designation' => 'VPI (Coufaivre)', 'emplacement_id' => null, 'uuid' => uniqid()],
            ['id' => 8, 'materiel_type_id' => 20, 'designation' => 'Transport (Glovelier)', 'emplacement_id' => null, 'uuid' => uniqid()],
        ]);

        // Relie chaque véhicule à l'emplacement (créé par EmplacementTableSeeder) qui
        // le représentait jusqu'ici par simple convention de nommage, sans lien en
        // base ; on aligne au passage sa désignation sur celle de l'article, qui en
        // est désormais la source de vérité.
        $emplacementParArticle = [
            1 => 5,  // Tonne-Pompe
            2 => 6,  // VPM
            3 => 7,  // Iveco
            4 => 8,  // Mowag
            5 => 11, // VPI (Glovelier)
            6 => 10, // Jeep
            7 => 9,  // VPI (Coufaivre)
            8 => 12, // Transport (Glovelier)
        ];

        foreach ($emplacementParArticle as $articleId => $emplacementId) {
            DB::table('emplacements')->where('id', $emplacementId)->update([
                'article_id' => $articleId,
                'designation' => DB::table('articles')->where('id', $articleId)->value('designation'),
            ]);
        }
    }
}
