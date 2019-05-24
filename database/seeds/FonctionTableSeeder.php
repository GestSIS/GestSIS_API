<?php

use Illuminate\Database\Seeder;

class FonctionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //TODO Update fonctions liste
        $fonctions = array(
            array('id' => '27', 'nom' => '0', 'abreviation' => 'BA', 'tri' => '10', 'cumulable' => 0),
            array('id' => '2', 'nom' => '1', 'abreviation' => 'PAR', 'tri' => '65', 'cumulable' => 0),
            array('id' => '3', 'nom' => '18', 'abreviation' => 'CG 1', 'tri' => '11', 'cumulable' => 0),
            array('id' => '4', 'nom' => '3', 'abreviation' => 'CG 2', 'tri' => '12', 'cumulable' => 0),
            array('id' => '5', 'nom' => '4', 'abreviation' => 'CI 1', 'tri' => '90', 'cumulable' => 0),
            array('id' => '6', 'nom' => '5', 'abreviation' => 'CI 2', 'tri' => '95', 'cumulable' => 0),
            array('id' => '7', 'nom' => '0', 'abreviation' => 'Cdt', 'tri' => '100', 'cumulable' => 0),
            array('id' => '8', 'nom' => '3', 'abreviation' => 'Ech rem', 'tri' => '80', 'cumulable' => 0),
            array('id' => '9', 'nom' => '18', 'abreviation' => 'MACH', 'tri' => '60', 'cumulable' => 0),
            array('id' => '10', 'nom' => '0', 'abreviation' => 'Four', 'tri' => '45', 'cumulable' => 0),
            array('id' => '11', 'nom' => '1', 'abreviation' => 'ELEC', 'tri' => '70', 'cumulable' => 0),
            array('id' => '12', 'nom' => '1', 'abreviation' => 'GC', 'tri' => '50', 'cumulable' => 0),
            array('id' => '13', 'nom' => '18', 'abreviation' => 'PAM', 'tri' => '55', 'cumulable' => 0),
            array('id' => '14', 'nom' => '5', 'abreviation' => 'DCH', 'tri' => '92', 'cumulable' => 0),
            array('id' => '15', 'nom' => '2', 'abreviation' => 'PAR', 'tri' => '66', 'cumulable' => 0),
            array('id' => '16', 'nom' => '0', 'abreviation' => 'BLS', 'tri' => '41', 'cumulable' => 0),
            array('id' => '17', 'nom' => '16', 'abreviation' => 'BA 1', 'tri' => '42', 'cumulable' => 0),
            array('id' => '18', 'nom' => '17', 'abreviation' => 'BA 2', 'tri' => '43', 'cumulable' => 0),
            array('id' => '19', 'nom' => '0', 'abreviation' => 'CG', 'tri' => '85', 'cumulable' => 0),
            array('id' => '20', 'nom' => '0', 'abreviation' => 'PR', 'tri' => '45', 'cumulable' => 0),
            array('id' => '21', 'nom' => '0', 'abreviation' => 'PR', 'tri' => '45', 'cumulable' => 0),
            array('id' => '22', 'nom' => '0', 'abreviation' => 'CDT', 'tri' => '45', 'cumulable' => 0),
        );

        foreach ($fonctions as $fonction) {
            DB::table('fonctions')->insert($fonction);
        }
    }
}
