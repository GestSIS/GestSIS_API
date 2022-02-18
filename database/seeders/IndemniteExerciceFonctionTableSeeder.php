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
    public function run()
    {
        $fonction = array(
            array('tarif' => 40, 'compte_id' => 2, 'fonction_id' => null, 'indemnite_exe_id' => 1),

            array('tarif' => 40, 'compte_id' => 2, 'fonction_id' => 1, 'indemnite_exe_id' => 10),
            array('tarif' => 20, 'compte_id' => 1,  'fonction_id' => 1, 'indemnite_exe_id' => 10),
            array('tarif' => 30, 'compte_id' => 2, 'fonction_id' => 2, 'indemnite_exe_id' => 10),
            array('tarif' => 40, 'compte_id' => 2, 'fonction_id' => 3, 'indemnite_exe_id' => 10),
            array('tarif' => 40, 'compte_id' => 1,  'fonction_id' => 3, 'indemnite_exe_id' => 10),
            array('tarif' => 40, 'compte_id' => 2, 'fonction_id' => 4, 'indemnite_exe_id' => 10),
            array('tarif' => 10, 'compte_id' => 1,  'fonction_id' => 4, 'indemnite_exe_id' => 10),
            array('tarif' => 80, 'compte_id' => 2, 'fonction_id' => 5, 'indemnite_exe_id' => 10),
            array('tarif' => 80, 'compte_id' => 1,  'fonction_id' => 5, 'indemnite_exe_id' => 10),
            array('tarif' => 30, 'compte_id' => 1,  'fonction_id' => 6, 'indemnite_exe_id' => 10),
            array('tarif' => 30, 'compte_id' => 2, 'fonction_id' => 7, 'indemnite_exe_id' => 10),
            array('tarif' => 25, 'compte_id' => 2, 'fonction_id' => 8, 'indemnite_exe_id' => 10),
            array('tarif' => 30, 'compte_id' => 1,  'fonction_id' => 9, 'indemnite_exe_id' => 10),


            array('type_unite_id' => 1, 'designation' => 'Exercice soirée', 'ecriture_categorie_id' => 1, 'par_fonction' => false),
            array('type_unite_id' => 1, 'designation' => 'Exercice matinée', 'ecriture_categorie_id' => 2, 'par_fonction' => false),
            array('type_unite_id' => 1, 'designation' => 'Matinée d\'instruction', 'ecriture_categorie_id' => 5, 'par_fonction' => false),
            array('type_unite_id' => 1, 'designation' => 'Soirée d\'instruction', 'ecriture_categorie_id' => 5, 'par_fonction' => false),
            array('type_unite_id' => 1, 'designation' => 'Journée d\'instruction', 'ecriture_categorie_id' => 5, 'par_fonction' => false),

            array('type_unite_id' => 1, 'designation' => 'Etat-major', 'ecriture_categorie_id' => 6, 'par_fonction' => false),
            array('type_unite_id' => 1, 'designation' => 'Comission / Bureau', 'ecriture_categorie_id' => 6, 'par_fonction' => false),
            array('type_unite_id' => 2, 'designation' => 'Séances externes', 'ecriture_categorie_id' => 7, 'par_fonction' => false),
            array('type_unite_id' => 1, 'designation' => 'Autorité de surveillance', 'ecriture_categorie_id' => 6, 'par_fonction' => false),

            array('type_unite_id' => 2, 'designation' => 'Exercice journée', 'ecriture_categorie_id' => 3, 'par_fonction' => true),
        );
        //FIXME: Need to update data to make it fit compatbilite V2

        foreach ($fonction as $item) {
            DB::table('indemnite_exercice_fonctions')->insert($item);
        }
    }
}
