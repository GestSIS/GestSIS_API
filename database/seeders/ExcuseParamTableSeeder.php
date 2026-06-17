<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExcuseParamTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('excuse_params')->insert([
            'actif'              => true,
            'delai_excuse'       => 7,
            'email_rappel'       => false,
            'texte_email_rappel' => '',
        ]);
    }
}
