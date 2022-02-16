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
        );

        foreach ($fonction as $item) {
            DB::table('indemnite_exercice_fonctions')->insert($item);
        }
    }
}
