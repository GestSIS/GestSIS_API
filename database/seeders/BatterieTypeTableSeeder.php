<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BatterieTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('batterie_types')->insert([
            ['id' => 1, 'nom' => 'A'],
            ['id' => 2, 'nom' => 'AA'],
            ['id' => 3, 'nom' => 'AAA'],
            ['id' => 4, 'nom' => '9V'],
            ['id' => 5, 'nom' => '6V LF'],
            ['id' => 6, 'nom' => 'MagLite'],
        ]);
    }
}
