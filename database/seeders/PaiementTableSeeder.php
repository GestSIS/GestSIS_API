<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaiementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('paiements')->insert([
            ['id' => 1, 'decompte_id' => 1, 'solde' => 300, 'indemnite' => 1000, 'frais_forfaitaire' => 93.4, 'frais_effectif' => 0.0, 'autre' => 0, 'avs_ac' => 0, 'total' => 1393.4, 'sapeur_id' => 1],
            ['id' => 2, 'decompte_id' => 1, 'solde' => 300, 'indemnite' => 1000, 'frais_forfaitaire' => 93.4, 'frais_effectif' => 0.0, 'autre' => 0, 'avs_ac' => 0, 'total' => 1393.4, 'sapeur_id' => 2],
            ['id' => 3, 'decompte_id' => 2, 'solde' => 300, 'indemnite' => 1000, 'frais_forfaitaire' => 93.4, 'frais_effectif' => 0.0, 'autre' => 0, 'avs_ac' => 100, 'total' => 1293.4, 'sapeur_id' => 1],
            ['id' => 4, 'decompte_id' => 2, 'solde' => 300, 'indemnite' => 1000, 'frais_forfaitaire' => 93.4, 'frais_effectif' => 0.0, 'autre' => 0, 'avs_ac' => 100, 'total' => 1293.4, 'sapeur_id' => 2],
        ]);
    }
}
