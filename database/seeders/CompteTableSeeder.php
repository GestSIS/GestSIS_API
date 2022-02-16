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
    public function run()
    {
        $comptes = array(
            array('id' => 1, 'numero' => '300', 'designation' => 'EM + Autorité de surveillance + Comissions', 'produit' => false),
            array('id' => 2, 'numero' => '30101.00', 'designation' => 'Personnel d\'exploitation - indemnités annuelles', 'produit' => false),
            array('id' => 3, 'numero' => '30101.01', 'designation' => 'Personnel d\'exploitation - soldes exercices', 'produit' => false),
            array('id' => 4, 'numero' => '30101.02', 'designation' => 'Personnel d\'exploitation - indemnités pour interventions', 'produit' => false),
            array('id' => 5, 'numero' => '305', 'designation' => 'Charges AVS', 'produit' => false),
            array('id' => 6, 'numero' => '3100', 'designation' => 'Matériel et fournitures de bureau', 'produit' => false),
            array('id' => 7, 'numero' => '31301.00', 'designation' => 'Frais de téléphones', 'produit' => false),
            array('id' => 8, 'numero' => '3170', 'designation' => 'Frais de déplacement, d\'utilisation et autres frais', 'produit' => false),
            array('id' => 9, 'numero' => '427', 'designation' => 'Amendes', 'produit' => true),
        );

        foreach ($comptes as $compte) {
            DB::table('comptes')->insert($compte);
        }
    }
}
