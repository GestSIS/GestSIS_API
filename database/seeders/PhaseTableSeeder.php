<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhaseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('phases')->insert([
            ['intervention_id' => 393, 'phase_type_id' => 1, 'debut' => '2019-01-01 12:00'],
            ['intervention_id' => 393, 'phase_type_id' => 2, 'debut' => '2019-01-01 12:30'],
        ]);
    }
}
