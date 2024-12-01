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
    public function run(): void
    {
        DB::table('ecriture_categories')->insert([
            ['id' => 1, 'designation' => 'Exercice Soirée', 'tri' => 1],
            ['id' => 2, 'designation' => 'Exercice matinée', 'tri' => 2],
            ['id' => 3, 'designation' => 'Exercice journée', 'tri' => 3],
            ['id' => 4, 'designation' => 'Intervention', 'tri' => 4],
            ['id' => 5, 'designation' => 'Formation', 'tri' => 5],
            ['id' => 6, 'designation' => 'EM & Comissions', 'tri' => 6],
            ['id' => 7, 'designation' => 'Séances', 'tri' => 7],
            ['id' => 8, 'designation' => 'Frais & indemnités annuelles', 'tri' => 8],
            ['id' => 9, 'designation' => 'Amende', 'tri' => 9],
            ['id' => 10, 'designation' => 'No stat', 'tri' => 10],
        ]);
    }
}
