<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            array('id' => 1, 'designation' => 'Contrôle PAR ', 'validity_duration' => 2, 'expirable' => true, 'tri' => 1),
            array('id' => 2, 'designation' => 'Contrôle C1', 'validity_duration' => 2, 'expirable' => false, 'tri' => 2),
            array('id' => 3, 'designation' => 'Vaccin', 'validity_duration' => 2, 'expirable' => true, 'tri' => 3),
            array('id' => 4, 'designation' => 'Examen d\'aptitude FSS', 'validity_duration' => 2, 'expirable' => true, 'tri' => 4),
            array('id' => 5, 'designation' => 'Examen BLS', 'validity_duration' => 2, 'expirable' => true, 'tri' => 5),
            array('id' => 6, 'designation' => 'Cours CPR', 'validity_duration' => 2, 'expirable' => true, 'tri' => 6),
        );

        foreach ($types as $type) {
            DB::table('controle_medical_types')->insert($type);
        }
    }
}
