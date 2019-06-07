<?php

use Illuminate\Database\Seeder;

class PhaseTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $phases = array(
            array('id' => 1,'designation' => 'intervention'),
            array('id' => 2,'designation' => 'entretien'),
        );

        foreach ($phases as $item) {
            DB::table('phase_types')->insert($item);
        }
    }
}
