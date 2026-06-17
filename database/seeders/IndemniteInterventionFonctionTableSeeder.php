<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndemniteInterventionFonctionTableSeeder extends Seeder
{
    public function run(): void
    {
        // Tarifs spécifiques par fonction pour le type "Intervention" (id=1)
        // Appliqués lorsque par_fonction=true sur le type d'indemnité
        DB::table('indemnite_intervention_fonctions')->insert([
            ['indemnite_int_id' => 1, 'fonction_id' => 1,  'tarif' => 40.00], // Commandant
            ['indemnite_int_id' => 1, 'fonction_id' => 2,  'tarif' => 35.00], // Vice-commandant
            ['indemnite_int_id' => 1, 'fonction_id' => 3,  'tarif' => 35.00], // Resp. Instruction
            ['indemnite_int_id' => 1, 'fonction_id' => 11, 'tarif' => 35.00], // Resp. Section
        ]);
    }
}
