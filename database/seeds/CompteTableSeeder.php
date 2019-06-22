<?php

use Illuminate\Database\Seeder;

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
            array('id' => 1, 'numero' => '300', 'designation' => 'EM + Autorité de surveillance + Comissions'),
            array('id' => 2, 'numero' => '30101.00', 'designation' => 'Personnel d\'exploitation - indemnités annuelles'),
            array('id' => 3, 'numero' => '30101.01', 'designation' => 'Personnel d\'exploitation - soldes exercices'),
            array('id' => 4, 'numero' => '30101.02', 'designation' => 'Personnel d\'exploitation - indemnités pour interventions'),
            array('id' => 5, 'numero' => '305', 'designation' => 'Charges AVS'),
            array('id' => 6, 'numero' => '3100', 'designation' => 'Matériel et fournitures de bureau'),
            array('id' => 7, 'numero' => '31301.00', 'designation' => 'Frais de téléphones'),
            array('id' => 8, 'numero' => '3170', 'designation' => 'Frais de déplacement, d\'utilisation et autres frais'),
            array('id' => 9, 'numero' => '427', 'designation' => 'Amendes'),
        );

        foreach ($comptes as $compte) {
            DB::table('comptes')->insert($compte);
        }
    }
}
