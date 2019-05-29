<?php

use Illuminate\Database\Seeder;

class ExcuseTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $excusesTypes = array(
            array('id' => 1, 'designation' => 'Excuse valable', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 1),
            array('id' => 2, 'designation' => 'Excuse orale uniquement', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 2),
            array('id' => 3, 'designation' => 'Professionnelle', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 3),
            array('id' => 4, 'designation' => 'Maladie / Accident', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 4),
            array('id' => 5, 'designation' => 'En congé', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 5),
            array('id' => 6, 'designation' => 'Exercice remplacé', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 6),
            array('id' => 7, 'designation' => 'Excuse non valable', 'abreviation' => '', 'amende' => 1, 'status' => 1, 'tri' => 7),
            array('id' => 8, 'designation' => 'Militaire', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 8),
            array('id' => 9, 'designation' => 'Fonction publique', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 9),
            array('id' => 10, 'designation' => 'A traiter', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 10),
            array('id' => 11, 'designation' => 'Non Amendable', 'abreviation' => '', 'amende' => 0, 'status' => 1, 'tri' => 11),
        );

        foreach ($excusesTypes as $excuseType) {
            DB::table('excuse_types')->insert($excuseType);
        }
    }
}
