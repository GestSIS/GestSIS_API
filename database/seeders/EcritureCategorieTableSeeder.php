<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EcritureCategorieTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = array(
            array('id' => 1, 'designation' => 'Exercice Soirée', 'tri' => 1),
            array('id' => 2, 'designation' => 'Exercice matinée', 'tri' => 2),
            array('id' => 3, 'designation' => 'Exercice journée', 'tri' => 3),
            array('id' => 4, 'designation' => 'Intervention', 'tri' => 4),
            array('id' => 5, 'designation' => 'Formation', 'tri' => 5),
            array('id' => 6, 'designation' => 'EM & Comissions', 'tri' => 6),
            array('id' => 7, 'designation' => 'Séances', 'tri' => 7),
            array('id' => 8, 'designation' => 'Frais & indemnités annuelles', 'tri' => 8),
        );

        foreach ($categories as $categorie) {
            DB::table('ecriture_categories')->insert($categorie);
        }
    }
}
