<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HangarTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Un hangar est un emplacement particulier : ces ids correspondent aux
        // emplacements 1-4 (casernes) créés par EmplacementTableSeeder, qui s'exécute
        // avant ce seeder.
        DB::table('hangars')->insert([
            ['id' => 1, 'localite_id' => 3, 'rue' => 'Colonel Hoffmeyer', 'no_rue' => '45'],
            ['id' => 2, 'localite_id' => 44, 'rue' => 'Rue de Saulcy', 'no_rue' => ''],
            ['id' => 3, 'localite_id' => 23, 'rue' => '', 'no_rue' => ''],
            ['id' => 4, 'localite_id' => 5, 'rue' => '', 'no_rue' => ''],
        ]);
    }
}
