<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsenceParamTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('absence_params')->insert([
            'actif' => true,
        ]);
    }
}
