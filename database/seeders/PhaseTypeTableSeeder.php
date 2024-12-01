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
    public function run(): void
    {
        DB::table('phase_types')->insert([
            ['id' => 1, 'designation' => 'intervention'],
            ['id' => 2, 'designation' => 'rétablissement'],
        ]);
    }
}
