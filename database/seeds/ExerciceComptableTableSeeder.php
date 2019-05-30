<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExerciceComptableTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exercices = array(
            array('id' => '1','annee' => 2017, 'debut'=> Carbon::parse('01-01-2017'), 'fin'=> Carbon::parse('31-12-2017'),'designation' => 'Exo 2017','boucle' => 0)
        );

        foreach ($exercices as $exercice) {
            DB::table('exercice_comptables')->insert($exercice);
        }
    }
}
