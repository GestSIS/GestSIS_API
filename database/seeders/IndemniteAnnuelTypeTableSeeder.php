<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndemniteAnnuelTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = array(
            array('id' => 1, 'cumulable' => False, 'ecriture_categorie_id' => 8, 'compte_id' => 2, 'designation' => 'Indemnité'), //Cdt
        );

        $indemnites = array(
            array('indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 2000, 'quantite' => 1, 'fonction_id' => 1), //Cdt
            array('indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 2000, 'quantite' => 1, 'fonction_id' => 3), //Resp inst
            array('indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 1000, 'quantite' => 1, 'fonction_id' => 2), //V-cdt
            array('indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 600, 'quantite' => 1, 'fonction_id' => 23), //Fourrier adjoint
            array('indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 1100, 'quantite' => 1, 'fonction_id' => 5), //Fourrier
            array('indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 2500, 'quantite' => 1, 'fonction_id' => 4), //Caissier
            array('indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 200, 'quantite' => 1, 'fonction_id' => 11), //Resp section
            array('indemnite_annuel_type_id' => 1, 'type_unite_id' => 3,  'montant' => 2000, 'quantite' => 1, 'fonction_id' => 17), //Chef mat
        );

        DB::table('indemnite_annuel_types')->insert($types);
        DB::table('indemnite_annuels')->insert($indemnites);
    }
}
