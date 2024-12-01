<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('missions')->insert([
            ['id' => 1, 'intervention_id' => 393, 'sapeur_id' => 1, 'titre' => 'Sauvetage', 'debut' => '2019-01-01 12:25', 'fin' => '2019-01-01 12:48', 'resume' => '2ème étage"'],
            ['id' => 2, 'intervention_id' => 393, 'sapeur_id' => 2, 'titre' => 'Extinction', 'debut' => '2019-01-01 12:30', 'fin' => '2019-01-01 12:35', 'resume' => 'Sous-sol'],
            ['id' => 3, 'intervention_id' => 393, 'sapeur_id' => 3, 'titre' => 'Ravitaillement', 'debut' => '2019-01-01 12:45', 'fin' => null, 'resume' => ''],
        ]);
    }
}
