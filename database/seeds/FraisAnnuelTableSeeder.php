<?php

use Illuminate\Database\Seeder;

class FraisAnnuelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $frais = array(
            array('designation' => 'Frais de déplacement', 'montant' => 0, 'fonction_id' => 1, 'compte_id' => 1),
            array('designation' => 'Frais de bureau', 'montant' => 100, 'fonction_id' => 1, 'compte_id' => 1),
            array('designation' => 'Frais de bureau', 'montant' => 0, 'fonction_id' => 2, 'compte_id' => 1),
            array('designation' => '', 'montant' => 0, 'fonction_id' => 3, 'compte_id' => 1),
            array('designation' => '', 'montant' => 0, 'fonction_id' => 4, 'compte_id' => 1),
            array('designation' => '', 'montant' => 0, 'fonction_id' => 5, 'compte_id' => 1),
        );

        foreach ($frais as $item) {
            DB::table('frais_annuels')->insert($item);
        }
    }
}
