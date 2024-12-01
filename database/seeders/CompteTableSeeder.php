<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('comptes')->insert([

            ['id' => 1, '1503.30002.00', 'Jetons de présence', 'produit' => false],
            ['id' => 2, '1503.30101.00', 'Personnel d\'exploitation - indemnités', 'produit' => false],
            ['id' => 3, '1503.30101.01', 'Personnel d\'exploitation - soldes exercices', 'produit' => false],
            ['id' => 4, '1503.30101.02', 'Personnel d\'exploitation - indemnités pour interventions', 'produit' => false],
            ['id' => 5, '1503.30501.00', 'Assurances AVS, AI et APG / AC', 'produit' => false],
            ['id' => 6, '1503.31120.00', 'Vêtements, literie, linge et rideaux', 'produit' => false],
            ['id' => 7, '1503.31309.05', 'Autres prestations de services - formation chauffeurs C1', 'produit' => false],
            ['id' => 8, '1503.31517.00', 'Déplacement, frais de véhicule', 'produit' => false],
            ['id' => 9, '1503.42709.00', 'Autres amendes', 'produit' => true],
            ['id' => 10, '1503.30101.03', 'Personnel d\'exploitation - entretien des véhicules et des hangars', 'produit' => false],
        ]);
    }
}
