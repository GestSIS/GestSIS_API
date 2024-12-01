<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('permis_types')->insert([
            ['type' => 'A'],
            ['type' => 'A1'],
            ['type' => 'B'],
            ['type' => 'B1'],
            ['type' => 'C'],
            ['type' => 'C1'],
            ['type' => 'C1 118'],
            ['type' => 'D'],
            ['type' => 'D1'],
            ['type' => 'BE'],
            ['type' => 'CE'],
            ['type' => 'C1E'],
            ['type' => 'DE'],
            ['type' => 'D1E'],
            ['type' => 'M'],
            ['type' => 'F'],
            ['type' => 'G'],
            ['type' => 'TPP'],
            ['type' => 'OACP'],
        ]);
    }
}
