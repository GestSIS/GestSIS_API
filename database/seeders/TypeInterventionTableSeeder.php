<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('type_interventions')->insert([
            ['id' => 1, 'stat_intervention_id' => 1, 'designation' => 'Feu véhicule', 'tri' => 1],
            ['id' => 2, 'stat_intervention_id' => 6, 'designation' => 'Alarme automatique', 'tri' => 2],
            ['id' => 3, 'stat_intervention_id' => 1, 'designation' => 'Feu bâtiment', 'tri' => 3],
            ['id' => 4, 'stat_intervention_id' => 1, 'designation' => 'Feu forêt', 'tri' => 4],
            ['id' => 6, 'stat_intervention_id' => 8, 'designation' => 'Fausse alarme', 'tri' => 6],
            ['id' => 7, 'stat_intervention_id' => 1, 'designation' => 'Feu cheminée', 'tri' => 7],
            ['id' => 8, 'stat_intervention_id' => 3, 'designation' => 'Pollution chimique', 'tri' => 8],
            ['id' => 9, 'stat_intervention_id' => 3, 'designation' => 'Pollution hydrocarbure', 'tri' => 9],
            ['id' => 10, 'stat_intervention_id' => 4, 'designation' => 'Sauvetage personnes', 'tri' => 10],
            ['id' => 11, 'stat_intervention_id' => 4, 'designation' => 'Sauvetage autres', 'tri' => 11],
            ['id' => 12, 'stat_intervention_id' => 5, 'designation' => 'Inondation', 'tri' => 12],
            ['id' => 13, 'stat_intervention_id' => 5, 'designation' => 'Élément naturels', 'tri' => 13],
            ['id' => 14, 'stat_intervention_id' => 7, 'designation' => 'Radioactivité', 'tri' => 14],
            ['id' => 15, 'stat_intervention_id' => 7, 'designation' => 'Fermentation de fourrages', 'tri' => 15],
            ['id' => 16, 'stat_intervention_id' => 7, 'designation' => 'Panne d\'ascenseur', 'tri' => 16],
            ['id' => 17, 'stat_intervention_id' => 7, 'designation' => 'Autre intervention', 'tri' => 17]
        ]);
    }
}
