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
        DB::table('articles')->insert([
            ['id' => 1, 'materiel_type_id' => 18, 'designation' => 'Tonne-Pompe', 'emplacement_id' => 1, 'uuid' => uniqid()],
            ['id' => 2, 'materiel_type_id' => 19, 'designation' => 'VPM', 'emplacement_id' => 1, 'uuid' => uniqid()],
            ['id' => 3, 'materiel_type_id' => 20, 'designation' => 'Iveco', 'emplacement_id' => 1, 'uuid' => uniqid()],
            ['id' => 4, 'materiel_type_id' => 20, 'designation' => 'Mowag', 'emplacement_id' => 1, 'uuid' => uniqid()],
            ['id' => 5, 'materiel_type_id' => 21, 'designation' => 'VPI (Glovelier)', 'emplacement_id' => 1, 'uuid' => uniqid()],
            ['id' => 6, 'materiel_type_id' => 20, 'designation' => 'Jeep', 'emplacement_id' => 1, 'uuid' => uniqid()],
            ['id' => 7, 'materiel_type_id' => 21, 'designation' => 'VPI (Coufaivre)', 'emplacement_id' => 1, 'uuid' => uniqid()],
            ['id' => 8, 'materiel_type_id' => 20, 'designation' => 'Transport (Glovelier)', 'emplacement_id' => 1, 'uuid' => uniqid()],
        ]);
    }
}
