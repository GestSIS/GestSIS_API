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
            array('id' => 1, 'designation' => 'Contrôle PAR ', 'duree_validite' => 2, 'expirable' => true, 'tri' => 1),
            array('id' => 2, 'designation' => 'Examen d\'aptitude FSS', 'duree_validite' => 2, 'expirable' => true, 'tri' => 2),
        );

        foreach ($types as $type) {
            DB::table('controle_medical_types')->insert($type);
        }
    }
}
