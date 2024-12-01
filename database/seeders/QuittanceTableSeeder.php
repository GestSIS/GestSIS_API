<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuittanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $quittances = array(
            array('id' => 1, 'sapeur_id' => 1, 'intervention_id' => 393),
            array('id' => 2, 'sapeur_id' => 2, 'intervention_id' => 393),
            array('id' => 3, 'sapeur_id' => 3, 'intervention_id' => 393)
        );


        foreach ($quittances as $item) {
            DB::table('quittances')->insert($item);
        }
    }
}
