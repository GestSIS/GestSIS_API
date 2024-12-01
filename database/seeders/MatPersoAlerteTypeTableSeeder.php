<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MatPersoAlerteTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $events = array(
            array('id' => 1, 'titre' => 'Lavage max pour tenue feu', 'description' => 'Nb lavage max recommandé atteint (8)', 'seuil_min' => 8, 'dernier' => false),
            array('id' => 2, 'titre' => 'Clé perdues', 'description' => 'Le dernier contrôle de la clé/badge est négatif', 'seuil_min' => 0, 'dernier' => true),
        );

        DB::table('materiel_alerte_types')->insert($events);

        $pour = array(
            array('id' => 1, 'materiel_alerte_type_id' => 1, 'materiel_event_type_id' => 1),
            array('id' => 2, 'materiel_alerte_type_id' => 2, 'materiel_event_type_id' => 3),
        );

        DB::table('materiel_alerte_type_pour')->insert($pour);
    }
}
