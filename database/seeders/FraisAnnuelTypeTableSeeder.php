<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FraisAnnuelTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = array(
            array('id' => 1, 'compte_id' => 6, 'ecriture_categorie_id' => 8, 'designation' => 'Frais de bureau'), //Cdt
            array('id' => 2, 'compte_id' => 7, 'ecriture_categorie_id' => 8, 'designation' => 'Frais de téléphone'), //Cdt
        );

        $frais = array(
            array('frais_annuel_type_id' => 1, 'type_unite_id' => 1,  'montant' => 150, 'quantite' => 12, 'fonction_id' => 1), //Cdt
            array('frais_annuel_type_id' => 1, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 3), //Resp inst
            array('frais_annuel_type_id' => 1, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 2), //V-cdt
            array('frais_annuel_type_id' => 1, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 23), //Fourrier adjoint
            array('frais_annuel_type_id' => 1, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 5), //Fourrier
            array('frais_annuel_type_id' => 1, 'type_unite_id' => 1,  'montant' => 100, 'quantite' => 12, 'fonction_id' => 4), //Caissier
            array('frais_annuel_type_id' => 1, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 17), //Chef mat

            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 35, 'quantite' => 12, 'fonction_id' => 1), //Cdt
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 35, 'quantite' => 12, 'fonction_id' => 3), //Resp inst
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 35, 'quantite' => 12, 'fonction_id' => 2), //V-cdt
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 23), //Fourrier adjoint
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 5), //Fourrier
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 4), //Caissier
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 11), //Resp section
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 12), //Officiers
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 13), //Officiers
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 35, 'quantite' => 12, 'fonction_id' => 17), //Chef mat
            array('frais_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 6), //Ajoints chef mat
        );

        DB::table('frais_annuel_types')->insert($types);
        DB::table('frais_annuels')->insert($frais);
    }
}
