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
            array('solde' => 40, 'indemnite' => 20, 'fonction_id' => 1, 'indemnite_exe_id' => 10),
            array('solde' => 30, 'indemnite' => 0, 'fonction_id' => 2, 'indemnite_exe_id' => 10),
            array('solde' => 40, 'indemnite' => 40, 'fonction_id' => 3, 'indemnite_exe_id' => 10),
            array('solde' => 40, 'indemnite' => 10, 'fonction_id' => 4, 'indemnite_exe_id' => 10),
            array('solde' => 80, 'indemnite' => 80, 'fonction_id' => 5, 'indemnite_exe_id' => 10),
            array('solde' => 0, 'indemnite' => 30, 'fonction_id' => 6, 'indemnite_exe_id' => 10),
            array('solde' => 30, 'indemnite' => 0, 'fonction_id' => 7, 'indemnite_exe_id' => 10),
            array('solde' => 25, 'indemnite' => 0, 'fonction_id' => 8, 'indemnite_exe_id' => 10),
            array('solde' => 0, 'indemnite' => 30, 'fonction_id' => 9, 'indemnite_exe_id' => 10),
        );

        foreach ($fonction as $item) {
            DB::table('indemnite_exercice_fonctions')->insert($item);
        }
    }
}
