<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisParamTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('sis_params')->insert([
            'id' => 1,
            'nom' => 'Sis Test',
            'rue' => 'Rue des rangiers',
            'numero' => '12',
            'district' => 'Delémont',
            'no_arrondissement' => '1',
            'telephone' => '021 123 12 12',
            'email' => 'gg@sis-test.ch',
            'iban' => 'CH56 8080 8012 1231 12',
            'bic' => 'RAIF22',
            'localite_id' => 1,
            'sapeur_id' => 1
        ]);
    }
}
