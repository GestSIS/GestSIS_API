<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmplacementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('emplacements')->insert([
            ['id' => 1, 'parent_id' => null, 'designation' => 'Bassecourt', 'est_etiquete' => true, 'tri' => 13, 'couleur_id' => 1, 'statut' => true],
            ['id' => 2, 'parent_id' => null, 'designation' => 'Glovelier', 'est_etiquete' => true, 'tri' => 13, 'couleur_id' => 1, 'statut' => true],
            ['id' => 3, 'parent_id' => null, 'designation' => 'Courfaivre', 'est_etiquete' => true, 'tri' => 13, 'couleur_id' => 1, 'statut' => true],
            ['id' => 4, 'parent_id' => null, 'designation' => 'Boécourt', 'est_etiquete' => true, 'tri' => 13, 'couleur_id' => 1, 'statut' => true],
            ['id' => 5, 'parent_id' => 1, 'designation' => 'Tonne-Pompe', 'est_etiquete' => false, 'tri' => 1, 'couleur_id' => 2, 'statut' => true],
            ['id' => 6, 'parent_id' => 1, 'designation' => 'VPM', 'est_etiquete' => false, 'tri' => 1, 'couleur_id' => 2, 'statut' => true],
            ['id' => 7, 'parent_id' => 1, 'designation' => 'Iveco', 'est_etiquete' => false, 'tri' => 1, 'couleur_id' => 2, 'statut' => true],
            ['id' => 8, 'parent_id' => 4, 'designation' => 'Mowag', 'est_etiquete' => false, 'tri' => 1, 'couleur_id' => 2, 'statut' => true],
            ['id' => 9, 'parent_id' => 3, 'designation' => 'VPI', 'est_etiquete' => false, 'tri' => 1, 'couleur_id' => 2, 'statut' => true],
            ['id' => 10, 'parent_id' => 3, 'designation' => 'Jeep', 'est_etiquete' => false, 'tri' => 1, 'couleur_id' => 2, 'statut' => true],
            ['id' => 11, 'parent_id' => 2, 'designation' => 'VPI', 'est_etiquete' => false, 'tri' => 1, 'couleur_id' => 2, 'statut' => true],
            ['id' => 12, 'parent_id' => 2, 'designation' => 'Transport', 'est_etiquete' => false, 'tri' => 1, 'couleur_id' => 2, 'statut' => true],
            ['id' => 13, 'parent_id' => null, 'designation' => 'Stock', 'est_etiquete' => true, 'tri' => 13, 'couleur_id' => 9, 'statut' => true],
        ]);
    }
}
