<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SapeurInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('intervention_sapeur')->insert([
            ['sapeur_id' => 1, 'intervention_id' => 393, 'debut' => '2019-01-01 12:00', 'fin' => '2019-01-01 12:15', 'piquet' => false],
            ['sapeur_id' => 1, 'intervention_id' => 393, 'debut' => '2019-01-01 12:30', 'fin' => '2019-01-01 12:45', 'piquet' => false],
            ['sapeur_id' => 2, 'intervention_id' => 393, 'debut' => '2019-01-01 12:15', 'fin' => '2019-01-01 12:45', 'piquet' => false],
            ['sapeur_id' => 3, 'intervention_id' => 393, 'debut' => '2019-01-01 12:00', 'fin' => '2019-01-01 12:30', 'piquet' => false],
            ['sapeur_id' => 2, 'intervention_id' => 393, 'debut' => '2019-01-01 13:00', 'fin' => '2019-01-01 13:30', 'piquet' => true],
            ['sapeur_id' => 3, 'intervention_id' => 393, 'debut' => '2019-01-01 12:30', 'fin' => '2019-01-01 13:15', 'piquet' => true],
        ]);
    }
}
