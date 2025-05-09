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
            ['id' => 1, 'materiel_type_id' => 18, 'remarque' => 'Tonne-Pompe', 'emplacement_id' => 1, 'uuid' => 'V2387667w1'],
            ['id' => 2, 'materiel_type_id' => 19, 'remarque' => 'VPM', 'emplacement_id' => 1, 'uuid' => 'V2387667w2'],
            ['id' => 3, 'materiel_type_id' => 20, 'remarque' => 'Iveco', 'emplacement_id' => 1, 'uuid' => 'V2387667w3'],
            ['id' => 4, 'materiel_type_id' => 20, 'remarque' => 'Mowag', 'emplacement_id' => 1, 'uuid' => 'V2387667w4'],
            ['id' => 5, 'materiel_type_id' => 21, 'remarque' => 'VPI', 'emplacement_id' => 1, 'uuid' => 'V2387667w5'],
            ['id' => 6, 'materiel_type_id' => 20, 'remarque' => 'Jeep', 'emplacement_id' => 1, 'uuid' => 'V2387667w6'],
            ['id' => 7, 'materiel_type_id' => 21, 'remarque' => 'VPI', 'emplacement_id' => 1, 'uuid' => 'V2387667w7'],
            ['id' => 8, 'materiel_type_id' => 20, 'remarque' => 'Transport', 'emplacement_id' => 1, 'uuid' => 'V2387667w8'],
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
