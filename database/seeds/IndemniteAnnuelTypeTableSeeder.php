<?php

use Illuminate\Database\Seeder;

class IndemniteAnnuelTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indemnites = array(
            array('montant' => 2000, 'ecriture_categorie_id' => 8, 'quantite' => 1, 'fonction_id' => 1, 'compte_id' => 2, 'designation' => 'Indemnité Cdt'),//Cdt
            array('montant' => 2000, 'ecriture_categorie_id' => 8, 'quantite' => 1, 'fonction_id' => 3, 'compte_id' => 2, 'designation' => 'Indemnité Resp inst'),//Resp inst
            array('montant' => 1000, 'ecriture_categorie_id' => 8, 'quantite' => 1, 'fonction_id' => 2, 'compte_id' => 2, 'designation' => 'Indemnité V-cdt'),//V-cdt
            array('montant' => 600, 'ecriture_categorie_id' => 8, 'quantite' => 1, 'fonction_id' => 23, 'compte_id' => 2, 'designation' => 'Indemnité Fourrier adjoint'),//Fourrier adjoint
            array('montant' => 1100, 'ecriture_categorie_id' => 8, 'quantite' => 1, 'fonction_id' => 5, 'compte_id' => 2, 'designation' => 'Indemnité Fourrier'),//Fourrier
            array('montant' => 2500, 'ecriture_categorie_id' => 8, 'quantite' => 1, 'fonction_id' => 4, 'compte_id' => 2, 'designation' => 'Indemnité Caissier'),//Caissier
            array('montant' => 200, 'ecriture_categorie_id' => 8, 'quantite' => 1, 'fonction_id' => 11, 'compte_id' => 2, 'designation' => 'Indemnité Resp section'),//Resp section
            array('montant' => 2000, 'ecriture_categorie_id' => 8, 'quantite' => 1, 'fonction_id' => 17, 'compte_id' => 2, 'designation' => 'Indemnité Chef mat'),//Chef mat
        );

        foreach ($indemnites as $item) {
            DB::table('indemnite_annuel_types')->insert($item);
        }
    }
}
