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
        DB::table('emplacements')->insert([
            ['id' => 9, 'designation' => 'Bassecourt', 'tri' => 9, 'couleur_id' => 2, 'statut' => 1],
            ['id' => 10, 'designation' => 'Glovelier', 'tri' => 10, 'couleur_id' => 2, 'statut' => 1],
            ['id' => 11, 'designation' => 'Courfaivre', 'tri' => 11, 'couleur_id' => 2, 'statut' => 1],
            ['id' => 12, 'designation' => 'Boécourt', 'tri' => 12, 'couleur_id' => 2, 'statut' => 1],
        ]);
        DB::table('hangars')->insert([
            ['id' => 9, 'localite_id' => 3, 'rue' => 'Colonel Hoffmeyer', 'no_rue' => '45'],
            ['id' => 10, 'localite_id' => 44, 'rue' => 'Rue de Saulcy', 'no_rue' => ''],
            ['id' => 11, 'localite_id' => 23, 'rue' => '', 'no_rue' => ''],
            ['id' => 12, 'localite_id' => 5, 'rue' => '', 'no_rue' => ''],
        ]);
    }
}
