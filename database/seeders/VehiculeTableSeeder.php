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
            ['id' => 1, 'designation' => 'Tonne-Pompe (Bassecourt)', 'tri' => 1, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 2, 'designation' => 'VPM (Bassecourt)', 'tri' => 2, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 3, 'designation' => 'Transport (Bassecourt)', 'tri' => 3, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 4, 'designation' => 'Transport (Boécourt)', 'tri' => 4, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 5, 'designation' => 'VPI (Courfaivre)', 'tri' => 5, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 6, 'designation' => 'Jeep (Courfaivre)', 'tri' => 6, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 7, 'designation' => 'VPI (Glovelier)', 'tri' => 7, 'couleur_id' => 1, 'statut' => 1],
            ['id' => 8, 'designation' => 'Transport (Glovelier)', 'tri' => 8, 'couleur_id' => 1, 'statut' => 1],
        ]);
        DB::table('vehicules')->insert([
            ['id' => 1, 'forfait' => 0, 'unite' => 0],
            ['id' => 2, 'forfait' => 0, 'unite' => 0],
            ['id' => 3, 'forfait' => 0, 'unite' => 0],
            ['id' => 4, 'forfait' => 0, 'unite' => 0],
            ['id' => 5, 'forfait' => 0, 'unite' => 0],
            ['id' => 6, 'forfait' => 0, 'unite' => 0],
            ['id' => 7, 'forfait' => 0, 'unite' => 0],
            ['id' => 8, 'forfait' => 0, 'unite' => 0]
        ]);
    }
}
