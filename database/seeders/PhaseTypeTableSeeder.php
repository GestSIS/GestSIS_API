<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            array('id' => 2,'designation' => 'rétablissement'),
        );

        foreach ($phases as $item) {
            DB::table('phase_types')->insert($item);
        }
    }
}
