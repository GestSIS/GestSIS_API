<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AspsmsParamTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('aspsms_params')->insert([
            'id' => 1,
            'username' => "fake",
            'password' => "demo",
            'origin' => "GestSIS",
        ]);
    }
}
