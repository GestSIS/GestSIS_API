<?php

use Illuminate\Database\Seeder;

class StatFederalTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stats = array(
            array('id' => 1, 'designation' => 'Lutte contre le feu', 'tri' => 1, 'status' => 1),
            array('id' => 2, 'designation' => 'Evénement dus à la nature', 'tri' => 2, 'status' => 1),
            array('id' => 3, 'designation' => 'Secours routier', 'tri' => 3, 'status' => 1),
            array('id' => 4, 'designation' => 'Assistance technique', 'tri' => 4, 'status' => 1),
            array('id' => 5, 'designation' => 'Défense hydrocarbure', 'tri' => 5, 'status' => 1),
            array('id' => 6, 'designation' => 'Défense chimique', 'tri' => 6, 'status' => 1),
            array('id' => 7, 'designation' => 'Défense radioprotection', 'tri' => 7, 'status' => 1),
            array('id' => 8, 'designation' => 'Intervention dans le domaine ferroviaire', 'tri' => 8, 'status' => 1),
            array('id' => 9, 'designation' => 'Fausses alarmes de détection incendie', 'tri' => 9, 'status' => 1),
            array('id' => 10, 'designation' => 'Interventions diverses', 'tri' => 10, 'status' => 1),
            array('id' => 11, 'designation' => 'Interventions sans alarme', 'tri' => 11, 'status' => 1),
        );

        foreach ($stats as $item) {
            DB::table('stat_federals')->insert($item);
        }
    }
}
