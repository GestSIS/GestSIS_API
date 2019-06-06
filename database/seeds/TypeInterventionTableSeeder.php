<?php

use Illuminate\Database\Seeder;

class TypeInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = array(
            array('id' => 1, 'stat_intervention_id' => 1,'designation' => 'Feu véhicule', 'tri' => 1),
            array('id' => 2, 'stat_intervention_id' => 1,'designation' => 'Alarme automatique', 'tri' => 2),
            array('id' => 3, 'stat_intervention_id' => 1,'designation' => 'Feu bâtiment', 'tri' => 3),
            array('id' => 4, 'stat_intervention_id' => 1,'designation' => 'Feu forêt', 'tri' => 4),
            array('id' => 5, 'stat_intervention_id' => 1,'designation' => 'Guêpes', 'tri' => 5),
            array('id' => 6, 'stat_intervention_id' => 1,'designation' => 'Fausse alarme', 'tri' => 6),
            array('id' => 7, 'stat_intervention_id' => 1,'designation' => 'Feu cheminée', 'tri' => 7),
            array('id' => 8, 'stat_intervention_id' => 1,'designation' => 'Pollution chimique', 'tri' => 8),
            array('id' => 9, 'stat_intervention_id' => 1,'designation' => 'Pollution hydrocarbure', 'tri' => 9),
            array('id' => 10, 'stat_intervention_id' => 1,'designation' => 'Sauvetage personnes', 'tri' => 10),
            array('id' => 11, 'stat_intervention_id' => 1,'designation' => 'Sauvetage autres', 'tri' => 11),
            array('id' => 12, 'stat_intervention_id' => 1,'designation' => 'Inondation', 'tri' => 12),
            array('id' => 13, 'stat_intervention_id' => 1,'designation' => 'Élément naturels', 'tri' => 13),
            array('id' => 14, 'stat_intervention_id' => 1,'designation' => 'Radioactivité', 'tri' => 14),
            array('id' => 15, 'stat_intervention_id' => 1,'designation' => 'Fermentation de fourrages', 'tri' => 15),
            array('id' => 16, 'stat_intervention_id' => 1,'designation' => 'Panne d\'ascenseur', 'tri' => 16),
            array('id' => 17, 'stat_intervention_id' => 1,'designation' => 'Autre intervention', 'tri' => 17)
        );

        foreach ($types as $item) {
            DB::table('type_interventions')->insert($item);
        }

    }
}
