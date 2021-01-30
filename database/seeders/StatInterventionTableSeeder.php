<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stats = array(
            array('id' => 1, 'Pas de stats' => 'stat 1', 'tri' => 1),
            array('id' => 2, 'Feux' => 'stat 2', 'tri' => 2),
            array('id' => 3, 'Guêpes' => 'stat 3', 'tri' => 3),
            array('id' => 4, 'Alarmes automatiques' => 'stat 4', 'tri' => 4),
            array('id' => 5, 'Divers' => 'stat 5', 'tri' => 5),
            array('id' => 6, 'Pollutions' => 'stat 6', 'tri' => 6),
            array('id' => 7, 'Sauvetages' => 'stat 7', 'tri' => 7),
            array('id' => 8, 'Inondations / Eléments naturels' => 'stat 8', 'tri' => 8),
        );

        foreach ($stats as $item) {
            DB::table('stat_interventions')->insert($item);
        }
    }
}
