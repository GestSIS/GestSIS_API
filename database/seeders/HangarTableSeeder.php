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
            ['id' => 1, 'designation' => 'Bassecourt', 'est_etiquete' => true, 'tri' => 9, 'couleur_id' => 2, 'statut' => 1],
            ['id' => 2, 'designation' => 'Glovelier', 'est_etiquete' => true, 'tri' => 10, 'couleur_id' => 2, 'statut' => 1],
            ['id' => 3, 'designation' => 'Courfaivre', 'est_etiquete' => true, 'tri' => 11, 'couleur_id' => 2, 'statut' => 1],
            ['id' => 4, 'designation' => 'Boécourt', 'est_etiquete' => true, 'tri' => 12, 'couleur_id' => 2, 'statut' => 1],
        ]);
        DB::table('hangars')->insert([
            ['id' => 1, 'localite_id' => 3, 'rue' => 'Colonel Hoffmeyer', 'no_rue' => '45'],
            ['id' => 2, 'localite_id' => 44, 'rue' => 'Rue de Saulcy', 'no_rue' => ''],
            ['id' => 3, 'localite_id' => 23, 'rue' => '', 'no_rue' => ''],
            ['id' => 4, 'localite_id' => 5, 'rue' => '', 'no_rue' => ''],
        ]);
    }
}
