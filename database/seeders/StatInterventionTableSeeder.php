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
            array('id' => 1, 'designation' => 'stat 1', 'tri' => 1),
            array('id' => 2, 'designation' => 'stat 2', 'tri' => 2),
            array('id' => 3, 'designation' => 'stat 3', 'tri' => 3),
            array('id' => 4, 'designation' => 'stat 4', 'tri' => 4),
        );

        foreach ($stats as $item) {
            DB::table('stat_interventions')->insert($item);
        }
    }
}
