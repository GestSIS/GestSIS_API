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
        DB::table('vehicules')->insert([
            ['id' => 1, 'designation' => 'Tonne-Pompe (Bassecourt)', 'forfait' => 0, 'unite' => 0, 'statut' => 1, 'tri' => 1],
            ['id' => 2, 'designation' => 'VPM (Bassecourt)', 'forfait' => 0, 'unite' => 0, 'statut' => 1, 'tri' => 2],
            ['id' => 3, 'designation' => 'Transport (Bassecourt)', 'forfait' => 0, 'unite' => 0, 'statut' => 1, 'tri' => 3],
            ['id' => 4, 'designation' => 'Transport (Boécourt)', 'forfait' => 0, 'unite' => 0, 'statut' => 1, 'tri' => 4],
            ['id' => 5, 'designation' => 'VPI (Courfaivre)', 'forfait' => 0, 'unite' => 0, 'statut' => 1, 'tri' => 5],
            ['id' => 6, 'designation' => 'Jeep (Courfaivre)', 'forfait' => 0, 'unite' => 0, 'statut' => 1, 'tri' => 6],
            ['id' => 7, 'designation' => 'VPI (Glovelier)', 'forfait' => 0, 'unite' => 0, 'statut' => 1, 'tri' => 7],
            ['id' => 8, 'designation' => 'Transport (Glovelier)', 'forfait' => 0, 'unite' => 0, 'statut' => 1, 'tri' => 8]
        ]);
    }
}
