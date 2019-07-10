<?php

use Illuminate\Database\Seeder;

class IndemniteExerciceTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indemnites = array(
            array('designation' => 'Exercice soirée', 'ecriture_categorie_id' => 1, 'compte_id' => 3, 'solde' => 40, 'indemnite' => 20, 'type_unite_id' => 2, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false),
            array('designation' => 'Exercice matinée', 'ecriture_categorie_id' => 2, 'compte_id' => 3, 'solde' => 30, 'indemnite' => 0, 'type_unite_id' => 1, 'solde_min' => 40, 'solde_min_pour' => 1, 'par_fonction' => false),
            array('designation' => 'Matinée d\'instruction', 'ecriture_categorie_id' => 5, 'compte_id' => 3, 'solde' => 40, 'indemnite' => 40, 'type_unite_id' => 2, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false),
            array('designation' => 'Soirée d\'instruction', 'ecriture_categorie_id' => 5, 'compte_id' => 3, 'solde' => 40, 'indemnite' => 10, 'type_unite_id' => 2, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false),
            array('designation' => 'Journée d\'instruction', 'ecriture_categorie_id' => 5, 'compte_id' => 3, 'solde' => 80, 'indemnite' => 80, 'type_unite_id' => 2, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false),

            array('designation' => 'Etat-major', 'ecriture_categorie_id' => 6, 'compte_id' => 1, 'solde' => 0, 'indemnite' => 30, 'type_unite_id' => 2, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false),
            array('designation' => 'Comission / Bureau', 'ecriture_categorie_id' => 6, 'compte_id' => 1, 'solde' => 30, 'indemnite' => 0, 'type_unite_id' => 2, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false),
            array('designation' => 'Séances externes', 'ecriture_categorie_id' => 7, 'compte_id' => 1, 'solde' => 25, 'indemnite' => 0, 'type_unite_id' => 1, 'solde_min' => 30, 'solde_min_pour' => 1, 'par_fonction' => false),
            array('designation' => 'Autorité de surveillance', 'ecriture_categorie_id' => 6, 'compte_id' => 1, 'solde' => 0, 'indemnite' => 30, 'type_unite_id' => 2, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => false),

            array('designation' => 'Exercice journée', 'ecriture_categorie_id' => 3, 'compte_id' => 1, 'solde' => 0, 'indemnite' => 30, 'type_unite_id' => 1, 'solde_min' => null, 'solde_min_pour' => null, 'par_fonction' => true),
        );

        foreach ($indemnites as $item) {
            DB::table('indemnite_exercice_types')->insert($item);
        }
    }
}
