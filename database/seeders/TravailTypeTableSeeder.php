<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TravailTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('travail_types')->insert([
            ['designation' => 'Roulage véhicule', 'actif' => true, 'type_unite_id' => 2, 'ecriture_categorie_id' => 10, 'lien_obligatoire' => false, 'lien_exercice' => false, 'lien_intervention' => false],
            ['designation' => 'Travaux hangars', 'actif' => true, 'type_unite_id' => 2, 'ecriture_categorie_id' => 10, 'lien_obligatoire' => false, 'lien_exercice' => false, 'lien_intervention' => false],
            ['designation' => 'Administratif', 'actif' => true, 'type_unite_id' => 2, 'ecriture_categorie_id' => 10, 'lien_obligatoire' => true, 'lien_exercice' => false, 'lien_intervention' => true],
        ]);

        DB::table('travail_type_fonctions')->insert([
            ['travail_type_id' => 1, 'type' => 1, 'tarif' => 25, 'compte_id' => 8, 'fonction_id' => null],
            ['travail_type_id' => 1, 'type' => 2, 'tarif' => 25, 'compte_id' => 6, 'fonction_id' => null],
            ['travail_type_id' => 2, 'type' => 1, 'tarif' => 25, 'compte_id' => 2, 'fonction_id' => null],
            ['travail_type_id' => 3, 'type' => 1, 'tarif' => 25, 'compte_id' => 2, 'fonction_id' => null],
        ]);
    }
}
