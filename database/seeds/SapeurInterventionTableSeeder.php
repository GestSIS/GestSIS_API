<?php

use Illuminate\Database\Seeder;

class SapeurInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sapeurs = array(
            array('id' => '1', 'sapeur_id' => '1', 'intervention_id' => '393', 'debut' => '2019-01-12', 'fin' => '2019-01-12', 'piquet' => false),
            array('id' => '2', 'sapeur_id' => '2', 'intervention_id' => '393', 'debut' => '2019-01-12', 'fin' => '2019-01-12', 'piquet' => false),
            array('id' => '3', 'sapeur_id' => '3', 'intervention_id' => '393', 'debut' => '2019-01-12', 'fin' => '2019-01-12', 'piquet' => false),
        );


        foreach ($sapeurs as $item) {
            DB::table('intervention_sapeur')->insert($item);
        }
    }
}
