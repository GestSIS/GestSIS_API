<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TravailTypeFonctionTableSeeder extends Seeder
{
    public function run(): void
    {
        // Tarifs spécifiques par fonction (complètent les tarifs par défaut fonction_id=null
        // déjà insérés dans TravailTypeTableSeeder)
        DB::table('travail_type_fonctions')->insert([
            // Roulage véhicule (id=1) — machiniste et resp. machines
            ['travail_type_id' => 1, 'type' => 1, 'tarif' => 30.00, 'compte_id' => 8, 'fonction_id' => 18], // Machiniste
            ['travail_type_id' => 1, 'type' => 1, 'tarif' => 30.00, 'compte_id' => 8, 'fonction_id' => 9],  // Resp. Mach.
            // Travaux hangars (id=2) — resp. matériel
            ['travail_type_id' => 2, 'type' => 1, 'tarif' => 30.00, 'compte_id' => 2, 'fonction_id' => 6],  // Resp. Matériel
        ]);
    }
}
