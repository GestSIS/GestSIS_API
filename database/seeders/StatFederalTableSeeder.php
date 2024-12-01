<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatFederalTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $stats = array(
            array('id' => 1, 'designation' => 'Lutte contre le feu', 'tri' => 1, 'statut' => 1),
            array('id' => 2, 'designation' => 'Evénement dus à la nature', 'tri' => 2, 'statut' => 1),
            array('id' => 3, 'designation' => 'Secours routier', 'tri' => 3, 'statut' => 1),
            array('id' => 4, 'designation' => 'Assistance technique', 'tri' => 4, 'statut' => 1),
            array('id' => 5, 'designation' => 'Défense hydrocarbure', 'tri' => 5, 'statut' => 1),
            array('id' => 6, 'designation' => 'Défense chimique', 'tri' => 6, 'statut' => 1),
            array('id' => 7, 'designation' => 'Défense radioprotection', 'tri' => 7, 'statut' => 1),
            array('id' => 8, 'designation' => 'Intervention dans le domaine ferroviaire', 'tri' => 8, 'statut' => 1),
            array('id' => 9, 'designation' => 'Fausses alarmes de détection incendie', 'tri' => 9, 'statut' => 1),
            array('id' => 10, 'designation' => 'Interventions diverses', 'tri' => 10, 'statut' => 1),
            array('id' => 11, 'designation' => 'Interventions sans alarme', 'tri' => 11, 'statut' => 1),
        );

        foreach ($stats as $item) {
            DB::table('stat_federals')->insert($item);
        }
    }
}
