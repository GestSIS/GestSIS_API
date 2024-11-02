<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiviliteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('civilites')->insert([
            ['designation' => 'Homme', 'forme_politesse' => 'Monsieur'],
            ['designation' => 'Femme', 'forme_politesse' => 'Madame'],
        ]);
    }
}
