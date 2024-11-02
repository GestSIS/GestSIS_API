<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndemniteExerciceFonctionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('indemnite_exercice_fonctions')->insert([
            ['tarif' => 40, 'compte_id' => 2, 'fonction_id' => 1, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 20, 'compte_id' => 1,  'fonction_id' => 1, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 30, 'compte_id' => 2, 'fonction_id' => 2, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 40, 'compte_id' => 2, 'fonction_id' => 3, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 40, 'compte_id' => 1,  'fonction_id' => 3, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 40, 'compte_id' => 2, 'fonction_id' => 4, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 10, 'compte_id' => 1,  'fonction_id' => 4, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 80, 'compte_id' => 2, 'fonction_id' => 5, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 80, 'compte_id' => 1,  'fonction_id' => 5, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 30, 'compte_id' => 1,  'fonction_id' => 6, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 30, 'compte_id' => 2, 'fonction_id' => 7, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 25, 'compte_id' => 2, 'fonction_id' => 8, 'indemnite_exe_id' => 10, 'type' => 1],
            ['tarif' => 30, 'compte_id' => 1,  'fonction_id' => 9, 'indemnite_exe_id' => 10, 'type' => 1],


            // ['type_unite_id' => 1, 'designation' => 'Exercice soirée', 'ecriture_categorie_id' => 1, 'compte_id' => 3, 'solde' => 40, 'indemnite' => 20, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false],
            ['tarif' => 40, 'compte_id' => 3,  'fonction_id' => null, 'indemnite_exe_id' => 1, 'type' => 1],
            ['tarif' => 20, 'compte_id' => 3,  'fonction_id' => null, 'indemnite_exe_id' => 1, 'type' => 2],
            // ['type_unite_id' => 1, 'designation' => 'Exercice matinée', 'ecriture_categorie_id' => 2, 'compte_id' => 3, 'solde' => 30, 'indemnite' => 0, 'solde_min' => 40, 'solde_min_pour' => 1, 'par_fonction' => false],
            ['tarif' => 30, 'compte_id' => 1,  'fonction_id' => null, 'indemnite_exe_id' => 2, 'type' => 1],
            // ['type_unite_id' => 1, 'designation' => 'Matinée d\'instruction', 'ecriture_categorie_id' => 5, 'compte_id' => 3, 'solde' => 40, 'indemnite' => 40, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false],
            ['tarif' => 40, 'compte_id' => 3,  'fonction_id' => null, 'indemnite_exe_id' => 3, 'type' => 1],
            ['tarif' => 40, 'compte_id' => 3,  'fonction_id' => null, 'indemnite_exe_id' => 3, 'type' => 2],
            // ['type_unite_id' => 1, 'designation' => 'Soirée d\'instruction', 'ecriture_categorie_id' => 5, 'compte_id' => 3, 'solde' => 40, 'indemnite' => 10, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false],
            ['tarif' => 40, 'compte_id' => 3,  'fonction_id' => null, 'indemnite_exe_id' => 4, 'type' => 1],
            ['tarif' => 10, 'compte_id' => 3,  'fonction_id' => null, 'indemnite_exe_id' => 4, 'type' => 2],
            // ['type_unite_id' => 1, 'designation' => 'Journée d\'instruction', 'ecriture_categorie_id' => 5, 'compte_id' => 3, 'solde' => 80, 'indemnite' => 80, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false],
            ['tarif' => 80, 'compte_id' => 3,  'fonction_id' => null, 'indemnite_exe_id' => 5, 'type' => 1],
            ['tarif' => 80, 'compte_id' => 3,  'fonction_id' => null, 'indemnite_exe_id' => 5, 'type' => 2],

            // ['type_unite_id' => 1, 'designation' => 'Etat-major', 'ecriture_categorie_id' => 6, 'compte_id' => 1, 'solde' => 0, 'indemnite' => 30, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false],
            ['tarif' => 30, 'compte_id' => 1,  'fonction_id' => null, 'indemnite_exe_id' => 6, 'type' => 2],
            // ['type_unite_id' => 1, 'designation' => 'Comission / Bureau', 'ecriture_categorie_id' => 6, 'compte_id' => 1, 'solde' => 30, 'indemnite' => 0, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false],
            ['tarif' => 30, 'compte_id' => 1,  'fonction_id' => null, 'indemnite_exe_id' => 7, 'type' => 1],
            // ['type_unite_id' => 2, 'designation' => 'Séances externes', 'ecriture_categorie_id' => 7, 'compte_id' => 1, 'solde' => 25, 'indemnite' => 0, 'solde_min' => 30, 'solde_min_pour' => 1, 'par_fonction' => false],
            ['tarif' => 25, 'compte_id' => 1,  'fonction_id' => null, 'indemnite_exe_id' => 8, 'type' => 1],
            // ['type_unite_id' => 1, 'designation' => 'Autorité de surveillance', 'ecriture_categorie_id' => 6, 'compte_id' => 1, 'solde' => 0, 'indemnite' => 30, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false],
            ['tarif' => 30, 'compte_id' => 1,  'fonction_id' => null, 'indemnite_exe_id' => 9, 'type' => 2],

<<<<<<< HEAD
            // ['type_unite_id' => 2, 'designation' => 'Exercice journée', 'ecriture_categorie_id' => 3, 'compte_id' => 1, 'solde' => 0, 'indemnite' => 30, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => true],
            ['tarif' => 30, 'compte_id' => 1,  'fonction_id' => null, 'indemnite_exe_id' => 10, 'type' => 2],
        ]);
=======
            // array('type_unite_id' => 2, 'designation' => 'Exercice journée', 'ecriture_categorie_id' => 3, 'compte_id' => 1, 'solde' => 0, 'indemnite' => 30, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => true),
            array('tarif' => 30, 'compte_id' => 1,  'fonction_id' => null, 'indemnite_exe_id' => 10, 'type' => 2),
        );

        DB::table('indemnite_exercice_fonctions')->insert($fonctions);
>>>>>>> 6be1bf0 (Update and improve seeders speed)
    }
}
