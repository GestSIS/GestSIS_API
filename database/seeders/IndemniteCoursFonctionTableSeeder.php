<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndemniteCoursFonctionTableSeeder extends Seeder
{
    public function run(): void
    {
        // Tarifs spécifiques par fonction (complètent les tarifs par défaut fonction_id=null
        // déjà insérés dans IndemniteCoursTypeTableSeeder)
        DB::table('indemnite_cours_fonctions')->insert([
            // Cours cantonaux (id=1) — cadres supérieurs
            ['indemnite_cours_id' => 1, 'type' => 1, 'tarif' => 150.00, 'type_unite_id' => 5, 'compte_id' => 7, 'fonction_id' => 1], // Commandant
            ['indemnite_cours_id' => 1, 'type' => 1, 'tarif' => 135.00, 'type_unite_id' => 5, 'compte_id' => 7, 'fonction_id' => 2], // Vice-commandant
            // Cours fédéraux (id=2) — cadres supérieurs
            ['indemnite_cours_id' => 2, 'type' => 1, 'tarif' => 250.00, 'type_unite_id' => 6, 'compte_id' => 7, 'fonction_id' => 1], // Commandant
            ['indemnite_cours_id' => 2, 'type' => 1, 'tarif' => 225.00, 'type_unite_id' => 6, 'compte_id' => 7, 'fonction_id' => 2], // Vice-commandant
        ]);
    }
}
