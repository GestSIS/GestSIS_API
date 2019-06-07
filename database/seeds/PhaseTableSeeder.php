<?php

use Illuminate\Database\Seeder;

class PhaseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $phases = array(
            array('intervention_id' => 393, 'phase_type_id' => 1, 'debut' => '2019-01-14 12:00', 'fin' => '2019-01-14 16:00'),
            array('intervention_id' => 393, 'phase_type_id' => 2, 'debut' => '2019-01-14 16:00', 'fin' => '2019-01-14 20:00'),
        );

        foreach ($phases as $item) {
            DB::table('phases')->insert($item);
        }
    }
}
