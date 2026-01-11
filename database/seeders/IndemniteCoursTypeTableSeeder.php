<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndemniteCoursTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('indemnite_cours_types')->insert([
            ['designation' => 'Cours cantonaux', 'ecriture_categorie_id' => 5],
            ['designation' => 'Cours fédéraux', 'ecriture_categorie_id' => 5],
        ]);

        DB::table('indemnite_cours_fonctions')->insert([
            ['indemnite_cours_id' => 1, 'type' => 1, 'tarif' => 120, 'type_unite_id' => 5, 'compte_id' => 7, 'fonction_id' => null],
            ['indemnite_cours_id' => 2, 'type' => 1, 'tarif' => 200, 'type_unite_id' => 6, 'compte_id' => 7, 'fonction_id' => null],
        ]);
    }
}
