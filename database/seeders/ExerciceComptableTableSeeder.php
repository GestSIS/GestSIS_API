<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            array('id' => '1','annee' => 2017, 'debut'=> Carbon::parse('01-01-2017'), 'fin'=> Carbon::parse('31-12-2017'),'designation' => 'Exo 2017','boucle' => 1),
            array('id' => '2','annee' => 2018, 'debut'=> Carbon::parse('01-01-2018'), 'fin'=> Carbon::parse('31-12-2018'),'designation' => 'Exo 2018','boucle' => 0),
            array('id' => '3','annee' => 2019, 'debut'=> Carbon::parse('01-01-2019'), 'fin'=> Carbon::parse('31-12-2019'),'designation' => 'Exo 2019','boucle' => 0),
            array('id' => '4','annee' => 2020, 'debut'=> Carbon::parse('01-01-2020'), 'fin'=> Carbon::parse('31-12-2020'),'designation' => 'Exo 2020','boucle' => 0)
        );

        foreach ($exercices as $exercice) {
            DB::table('exercice_comptables')->insert($exercice);
        }
    }
}
