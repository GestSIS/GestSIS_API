<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciceComptableTableSeeder extends Seeder
{
    public function run(): void
    {
        $anneeEnCours = Carbon::now()->year;

        DB::table('exercice_comptables')->insert([
            ['id' => 1, 'annee' => 2017, 'debut' => Carbon::parse('01-01-2017'), 'fin' => Carbon::parse('31-12-2017'), 'designation' => 'Exo 2017', 'boucle' => 0],
            ['id' => 2, 'annee' => 2018, 'debut' => Carbon::parse('01-01-2018'), 'fin' => Carbon::parse('31-12-2018'), 'designation' => 'Exo 2018', 'boucle' => 0],
            ['id' => 3, 'annee' => 2019, 'debut' => Carbon::parse('01-01-2019'), 'fin' => Carbon::parse('31-12-2019'), 'designation' => 'Exo 2019', 'boucle' => 0],
            ['id' => 4, 'annee' => $anneeEnCours, 'debut' => Carbon::create($anneeEnCours, 1, 1), 'fin' => Carbon::create($anneeEnCours, 12, 31), 'designation' => "Exo $anneeEnCours", 'boucle' => 0],
        ]);
    }
}
