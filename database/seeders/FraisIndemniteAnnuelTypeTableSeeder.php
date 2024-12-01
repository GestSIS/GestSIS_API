<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FraisIndemniteAnnuelTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('frais_indemnite_annuel_types')->insert([
            ['id' => 1, 'cumulable' => False, 'compte_id' => 2, 'type' => 2, 'ecriture_categorie_id' => 8, 'designation' => 'Indemnité'], //Cdt
            ['id' => 2, 'cumulable' => False, 'compte_id' => 6, 'type' => 3, 'ecriture_categorie_id' => 8, 'designation' => 'Frais de bureau'], //Cdt
            ['id' => 3, 'cumulable' => False, 'compte_id' => 7, 'type' => 3, 'ecriture_categorie_id' => 8, 'designation' => 'Frais de téléphone'], //Cdt
        ]);

        DB::table('frais_indemnite_annuels')->insert([
            ['frais_indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 2000, 'quantite' => 1, 'fonction_id' => 1], //Cdt
            ['frais_indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 2000, 'quantite' => 1, 'fonction_id' => 3], //Resp inst
            ['frais_indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 1000, 'quantite' => 1, 'fonction_id' => 2], //V-cdt
            ['frais_indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 600, 'quantite' => 1, 'fonction_id' => 23], //Fourrier adjoint
            ['frais_indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 1100, 'quantite' => 1, 'fonction_id' => 5], //Fourrier
            ['frais_indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 2500, 'quantite' => 1, 'fonction_id' => 4], //Caissier
            ['frais_indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 200, 'quantite' => 1, 'fonction_id' => 11], //Resp section
            ['frais_indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 2000, 'quantite' => 1, 'fonction_id' => 17], //Chef mat

            ['frais_indemnite_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 150, 'quantite' => 12, 'fonction_id' => 1], //Cdt
            ['frais_indemnite_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 3], //Resp inst
            ['frais_indemnite_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 2], //V-cdt
            ['frais_indemnite_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 23], //Fourrier adjoint
            ['frais_indemnite_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 5], //Fourrier
            ['frais_indemnite_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 100, 'quantite' => 12, 'fonction_id' => 4], //Caissier
            ['frais_indemnite_annuel_type_id' => 2, 'type_unite_id' => 1,  'montant' => 50, 'quantite' => 12, 'fonction_id' => 17], //Chef mat

            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 35, 'quantite' => 12, 'fonction_id' => 1], //Cdt
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 35, 'quantite' => 12, 'fonction_id' => 3], //Resp inst
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 35, 'quantite' => 12, 'fonction_id' => 2], //V-cdt
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 23], //Fourrier adjoint
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 5], //Fourrier
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 4], //Caissier
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 11], //Resp section
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 12], //Officiers
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 13], //Officiers
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 35, 'quantite' => 12, 'fonction_id' => 17], //Chef mat
            ['frais_indemnite_annuel_type_id' => 3, 'type_unite_id' => 1,  'montant' => 25, 'quantite' => 12, 'fonction_id' => 6], //Ajoints chef mat
        ]);
    }
}
