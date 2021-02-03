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
            array('id' => 1, 'designation' => 'Feux', 'tri' => 1),
            array('id' => 2, 'designation' => 'Guêpes', 'tri' => 2),
            array('id' => 3, 'designation' => 'Pollutions', 'tri' => 3),
            array('id' => 4, 'designation' => 'Sauvetages', 'tri' => 4),
            array('id' => 5, 'designation' => 'Inondations / Elements naturels', 'tri' => 5),
            array('id' => 6, 'designation' => 'Alarmes automatiques', 'tri' => 6),
            array('id' => 7, 'designation' => 'Divers', 'tri' => 7),
            array('id' => 8, 'designation' => 'Pas de statistiques', 'tri' => 8),
        );

        foreach ($stats as $item) {
            DB::table('stat_interventions')->insert($item);
        }
    }
}
