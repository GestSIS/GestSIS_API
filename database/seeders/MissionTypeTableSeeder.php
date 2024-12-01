<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $missions = array(
            array('titre' => 'Alimentation tonne'),
            array('titre' => 'Aspiration'),
            array('titre' => 'Circulation'),
            array('titre' => 'Extinction'),
            array('titre' => 'Mise en place échelle'),
            array('titre' => 'Recherche de personnes'),
            array('titre' => 'Sauvetage'),
            array('titre' => 'Securisation')
        );

        foreach ($missions as $item) {
            DB::table('mission_types')->insert($item);
        }
    }
}
