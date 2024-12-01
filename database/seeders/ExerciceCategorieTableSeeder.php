<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciceCategorieTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('exercice_categories')->insert([
            ['id' => 1, 'designation' => 'Exercice', 'amendable' => 1, 'duree_base' => 180, 'statut' => 1, 'tri' => 1],
            ['id' => 2, 'designation' => 'Etat-major', 'amendable' => 0, 'duree_base' => 120, 'statut' => 1, 'tri' => 2],
            ['id' => 3, 'designation' => 'Commission', 'amendable' => 0, 'duree_base' => 120, 'statut' => 1, 'tri' => 3],
            ['id' => 4, 'designation' => 'Bureau', 'amendable' => 0, 'duree_base' => 120, 'statut' => 1, 'tri' => 4],
            ['id' => 5, 'designation' => 'Inspection', 'amendable' => 0, 'duree_base' => 120, 'statut' => 1, 'tri' => 5],
            ['id' => 6, 'designation' => 'Divers - Information', 'amendable' => 0, 'duree_base' => 120, 'statut' => 1, 'tri' => 6],
            ['id' => 7, 'designation' => 'Autorité de surveillance', 'amendable' => 0, 'duree_base' => 120, 'statut' => 1, 'tri' => 7],
            ['id' => 8, 'designation' => 'Exercice spécifique', 'amendable' => 1, 'duree_base' => 180, 'statut' => 1, 'tri' => 8],
            ['id' => 9, 'designation' => 'Séance divers', 'amendable' => 0, 'duree_base' => 120, 'statut' => 1, 'tri' => 9],
            ['id' => 10, 'designation' => 'Préparation exercices', 'amendable' => 0, 'duree_base' => 120, 'statut' => 1, 'tri' => 10],
        ]);
    }
}
