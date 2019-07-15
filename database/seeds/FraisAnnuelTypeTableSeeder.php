<?php

use Illuminate\Database\Seeder;

class FraisAnnuelTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $frais = array(
            array('compte_id' => 6, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 150, 'quantite' => 12, 'fonction_id' => 1, 'designation' => 'Frais de bureau'),//Cdt
            array('compte_id' => 6, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 50, 'quantite' => 12, 'fonction_id' => 3, 'designation' => 'Frais de bureau'),//Resp inst
            array('compte_id' => 6, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 50, 'quantite' => 12, 'fonction_id' => 2, 'designation' => 'Frais de bureau'),//V-cdt
            array('compte_id' => 6, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 50, 'quantite' => 12, 'fonction_id' => 23, 'designation' => 'Frais de bureau'),//Fourrier adjoint
            array('compte_id' => 6, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 50, 'quantite' => 12, 'fonction_id' => 5, 'designation' => 'Frais de bureau'),//Fourrier
            array('compte_id' => 6, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 100, 'quantite' => 12, 'fonction_id' => 4, 'designation' => 'Frais de bureau'),//Caissier
            array('compte_id' => 6, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 50, 'quantite' => 12, 'fonction_id' => 17, 'designation' => 'Frais de bureau'),//Chef mat

            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 35, 'quantite' => 12, 'fonction_id' => 1, 'designation' => 'Frais de téléphone'),//Cdt
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 35, 'quantite' => 12, 'fonction_id' => 3, 'designation' => 'Frais de téléphone'),//Resp inst
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 35, 'quantite' => 12, 'fonction_id' => 2, 'designation' => 'Frais de téléphone'),//V-cdt
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 25, 'quantite' => 12, 'fonction_id' => 23, 'designation' => 'Frais de téléphone'),//Fourrier adjoint
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 25, 'quantite' => 12, 'fonction_id' => 5, 'designation' => 'Frais de téléphone'),//Fourrier
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 25, 'quantite' => 12, 'fonction_id' => 4, 'designation' => 'Frais de téléphone'),//Caissier
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 25, 'quantite' => 12, 'fonction_id' => 11, 'designation' => 'Frais de téléphone'),//Resp section
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 25, 'quantite' => 12, 'fonction_id' => 12, 'designation' => 'Frais de téléphone'),//Officiers
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 25, 'quantite' => 12, 'fonction_id' => 13, 'designation' => 'Frais de téléphone'),//Officiers
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 35, 'quantite' => 12, 'fonction_id' => 17, 'designation' => 'Frais de téléphone'),//Chef mat
            array('compte_id' => 7, 'type_unite_id' => 1, 'ecriture_categorie_id' => 8, 'montant' => 25, 'quantite' => 12, 'fonction_id' => 6, 'designation' => 'Frais de téléphone'),//Ajoints chef mat
        );

        foreach ($frais as $item) {
            DB::table('frais_annuel_types')->insert($item);
        }
    }
}
