<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TuyauDiametreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('tuyau_diametres')->insert([
            ['id' => 1, 'diametre' => 40],
            ['id' => 2, 'diametre' => 55],
            ['id' => 3, 'diametre' => 75],
            ['id' => 4, 'diametre' => 110],
        ]);
    }
}
