<?php

use Illuminate\Database\Seeder;

class MissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $missions = array(
            array('id' => 1, 'intervention_id' => 393, 'sapeur_id' => 1, 'titre' => 'Sauvetage 1', 'debut' => '2019-12-01 12:25', 'fin' => '2019-12-01 12:25', 'resume' => ''),
            array('id' => 2, 'intervention_id' => 393, 'sapeur_id' => 2, 'titre' => 'Sauvetage 2', 'debut' => '2019-12-01 12:25', 'fin' => '2019-12-01 12:25', 'resume' => ''),
            array('id' => 3, 'intervention_id' => 393, 'sapeur_id' => 3, 'titre' => 'Sauvetage 3', 'debut' => '2019-12-01 12:25', 'fin' => '2019-12-01 12:25', 'resume' => ''),
        );

        foreach ($missions as $item) {
            DB::table('missions')->insert($item);
        }
    }
}
