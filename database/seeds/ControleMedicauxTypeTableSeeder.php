<?php

use Illuminate\Database\Seeder;

class ControleMedicauxTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = array(
            array('id' => 1, 'designation' => 'Contrôle PAR ', 'tri' => 1),
            array('id' => 2, 'designation' => 'Contrôle C1', 'tri' => 2),
            array('id' => 3, 'designation' => 'Vaccin', 'tri' => 3),
            array('id' => 4, 'designation' => 'Examen d\'aptitude FSS', 'tri' => 4),
            array('id' => 5, 'designation' => 'Examen BLS', 'tri' => 5),
            array('id' => 6, 'designation' => 'Cours CPR', 'tri' => 6),
        );

        foreach ($types as $type) {
            DB::table('controle_medical_types')->insert($type);
        }
    }
}
