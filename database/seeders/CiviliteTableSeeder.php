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
            ['id' => 1, 'designation' => 'Homme', 'forme_politesse' => 'Monsieur'],
            ['id' => 2, 'designation' => 'Femme', 'forme_politesse' => 'Madame'],
            ['id' => 3, 'designation' => 'Non-binaire', 'forme_politesse' => ''],
        ]);
    }
}
