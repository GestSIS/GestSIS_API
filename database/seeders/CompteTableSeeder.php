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
            ['id' => 1, 'numero' => '1503.30002.00', 'designation' => 'Jetons de présence', 'produit' => false],
            ['id' => 2, 'numero' => '1503.30101.00', 'designation' => 'Personnel d\'exploitation - indemnités', 'produit' => false],
            ['id' => 3, 'numero' => '1503.30101.01', 'designation' => 'Personnel d\'exploitation - soldes exercices', 'produit' => false],
            ['id' => 4, 'numero' => '1503.30101.02', 'designation' => 'Personnel d\'exploitation - indemnités pour interventions', 'produit' => false],
            ['id' => 5, 'numero' => '1503.30501.00', 'designation' => 'Assurances AVS, AI et APG / AC', 'produit' => false],
            ['id' => 6, 'numero' => '1503.31120.00', 'designation' => 'Vêtements, literie, linge et rideaux', 'produit' => false],
            ['id' => 7, 'numero' => '1503.31309.05', 'designation' => 'Autres prestations de services - formation chauffeurs C1', 'produit' => false],
            ['id' => 8, 'numero' => '1503.31517.00', 'designation' => 'Déplacement, frais de véhicule', 'produit' => false],
            ['id' => 9, 'numero' => '1503.42709.00', 'designation' => 'Autres amendes', 'produit' => true],
            ['id' => 10, 'numero' => '1503.30101.03', 'designation' => 'Personnel d\'exploitation - entretien des véhicules et des hangars', 'produit' => false],
        ]);
    }
}
