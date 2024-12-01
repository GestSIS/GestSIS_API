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
    public function run(): void
    {
        DB::table('stat_interventions')->insert([
            ['id' => 1, 'designation' => 'Feux', 'tri' => 1],
            ['id' => 3, 'designation' => 'Pollutions', 'tri' => 2],
            ['id' => 4, 'designation' => 'Sauvetages', 'tri' => 3],
            ['id' => 5, 'designation' => 'Inondations / Elements naturels', 'tri' => 4],
            ['id' => 6, 'designation' => 'Alarmes automatiques', 'tri' => 5],
            ['id' => 7, 'designation' => 'Divers', 'tri' => 6],
            ['id' => 8, 'designation' => 'Pas de statistiques', 'tri' => 7],
        ]);
    }
}
