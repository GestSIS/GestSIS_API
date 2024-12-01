<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvsParamTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('avs_params')->insert([
            'id' => 1,
            'taux_avs' => 0.125,
            'taux_ac' => 0.22,
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000,
            'franchise_imposition_cantonale' => 8000,
            'compte_id' => 5,
            'ecriture_categorie_id' => 10,
        ]);
    }
}
