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
        DB::table('emplacements')->insert([
            ['id' => 5, 'parent_id' => 1, 'est_etiquete' => true, 'designation' => 'Tonne-Pompe', 'tri' => 2, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 6, 'parent_id' => 1, 'est_etiquete' => true, 'designation' => 'VPM', 'tri' => 3, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 7, 'parent_id' => 1, 'est_etiquete' => true, 'designation' => 'Iveco', 'tri' => 4, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 8, 'parent_id' => 4, 'est_etiquete' => true, 'designation' => 'Mowag', 'tri' => 6, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 9, 'parent_id' => 3, 'est_etiquete' => true, 'designation' => 'VPI', 'tri' => 8, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 10, 'parent_id' => 3, 'est_etiquete' => true, 'designation' => 'Jeep', 'tri' => 9, 'couleur_id' => 1, 'statut' => 0],
            ['id' => 11, 'parent_id' => 2, 'est_etiquete' => true, 'designation' => 'VPI', 'tri' => 11, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 12, 'parent_id' => 2, 'est_etiquete' => true, 'designation' => 'Transport', 'tri' => 12, 'couleur_id' => 1, 'statut' => 1],
        ]);
        DB::table('vehicules')->insert([
            ['id' => 5, 'forfait' => 0, 'unite' => 0],
            ['id' => 6, 'forfait' => 0, 'unite' => 0],
            ['id' => 7, 'forfait' => 0, 'unite' => 0],
            ['id' => 8, 'forfait' => 0, 'unite' => 0],
            ['id' => 9, 'forfait' => 0, 'unite' => 0],
            ['id' => 10, 'forfait' => 0, 'unite' => 0],
            ['id' => 11, 'forfait' => 0, 'unite' => 0],
            ['id' => 12, 'forfait' => 0, 'unite' => 0]
        ]);
    }
}
