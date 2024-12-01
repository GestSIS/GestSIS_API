<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndemniteExerciceTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('indemnite_exercice_types')->insert([
            ['type_unite_id' => 1, 'designation' => 'Exercice soirée', 'ecriture_categorie_id' => 1, 'par_fonction' => false],
            ['type_unite_id' => 1, 'designation' => 'Exercice matinée', 'ecriture_categorie_id' => 2, 'par_fonction' => false],
            ['type_unite_id' => 1, 'designation' => 'Matinée d\'instruction', 'ecriture_categorie_id' => 5, 'par_fonction' => false],
            ['type_unite_id' => 1, 'designation' => 'Soirée d\'instruction', 'ecriture_categorie_id' => 5, 'par_fonction' => false],
            ['type_unite_id' => 1, 'designation' => 'Journée d\'instruction', 'ecriture_categorie_id' => 5, 'par_fonction' => false],

            ['type_unite_id' => 1, 'designation' => 'Etat-major', 'ecriture_categorie_id' => 6, 'par_fonction' => false],
            ['type_unite_id' => 1, 'designation' => 'Comission / Bureau', 'ecriture_categorie_id' => 6, 'par_fonction' => false],
            ['type_unite_id' => 2, 'designation' => 'Séances externes', 'ecriture_categorie_id' => 7, 'par_fonction' => false],
            ['type_unite_id' => 1, 'designation' => 'Autorité de surveillance', 'ecriture_categorie_id' => 6, 'par_fonction' => false],

            ['type_unite_id' => 2, 'designation' => 'Exercice journée', 'ecriture_categorie_id' => 3, 'par_fonction' => true],
        ]);
    }
}
