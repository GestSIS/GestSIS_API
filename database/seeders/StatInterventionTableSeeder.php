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
            array('id' => 1, 'designation' => 'Pas de stats', 'tri' => 1),
            array('id' => 2, 'designation' => 'Feux', 'tri' => 2),
            array('id' => 3, 'designation' => 'Guêpes', 'tri' => 3),
            array('id' => 4, 'designation' => 'Alarmes automatiques', 'tri' => 4),
            array('id' => 5, 'designation' => 'Divers', 'tri' => 5),
            array('id' => 6, 'designation' => 'Pollutions', 'tri' => 6),
            array('id' => 7, 'designation' => 'Sauvetages', 'tri' => 7),
            array('id' => 8, 'designation' => 'Inondations / Eléments naturels', 'tri' => 8),
        );

        foreach ($stats as $item) {
            DB::table('stat_interventions')->insert($item);
        }
    }
}
