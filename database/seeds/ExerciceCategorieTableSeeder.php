<?php

use Illuminate\Database\Seeder;

class ExerciceCategorieTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = array(
            array('id' => 1, 'designation' => 'Exercice', 'amendable' => 0, 'duree_base' => 180, 'status' => 1, 'tri' => 1),
            array('id' => 2, 'designation' => 'Etat-major', 'amendable' => 0, 'duree_base' => 120, 'status' => 1, 'tri' => 2),
            array('id' => 3, 'designation' => 'Commission', 'amendable' => 0, 'duree_base' => 120, 'status' => 1, 'tri' => 3),
            array('id' => 4, 'designation' => 'Bureau', 'amendable' => 0, 'duree_base' => 120, 'status' => 1, 'tri' => 4),
            array('id' => 5, 'designation' => 'Inspection', 'amendable' => 0, 'duree_base' => 120, 'status' => 1, 'tri' => 5),
            array('id' => 6, 'designation' => 'Divers - Information', 'amendable' => 0, 'duree_base' => 120, 'status' => 1, 'tri' => 6),
            array('id' => 7, 'designation' => 'Autorité de surveillance', 'amendable' => 0, 'duree_base' => 120, 'status' => 1, 'tri' => 7),
            array('id' => 8, 'designation' => 'Exercice spécifique', 'amendable' => 0, 'duree_base' => 180, 'status' => 1, 'tri' => 8),
            array('id' => 9, 'designation' => 'Séance divers', 'amendable' => 0, 'duree_base' => 120, 'status' => 1, 'tri' => 9),
            array('id' => 10, 'designation' => 'Préparation exercices', 'amendable' => 0, 'duree_base' => 120, 'status' => 1, 'tri' => 10),
            array('id' => 11, 'designation' => 'Frais annuels', 'amendable' => 0, 'duree_base' => 60, 'status' => 1, 'tri' => 11),
            array('id' => 1000000, 'designation' => 'Indemnité annuelle', 'amendable' => 0, 'duree_base' => 60, 'status' => 1, 'tri' => 12),
        );

        foreach($categories as $categorie){
            DB::table('exercice_categories')->insert($categorie);
        }
    }
}
