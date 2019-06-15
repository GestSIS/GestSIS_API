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
            array('id' => 1, 'intervention_id' => 393, 'sapeur_id' => 1, 'titre' => 'Sauvetage', 'debut' => '2019-12-01 12:25', 'fin' => '2019-12-01 12:48', 'resume' => '2ème étage"'),
            array('id' => 2, 'intervention_id' => 393, 'sapeur_id' => 2, 'titre' => 'Extinction', 'debut' => '2019-12-01 12:30', 'fin' => '2019-12-01 12:35', 'resume' => 'Sous-sol'),
            array('id' => 3, 'intervention_id' => 393, 'sapeur_id' => 3, 'titre' => 'Ravitaillement', 'debut' => '2019-12-01 12:45', 'fin' => null, 'resume' => ''),
        );

        foreach ($missions as $item) {
            DB::table('missions')->insert($item);
        }
    }
}
