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
    public function run(): void
    {
        DB::table('exercice_comptables')->insert([
            ['id' => 1, 'annee' => 2017, 'debut' => Carbon::parse('01-01-2017'), 'fin' => Carbon::parse('31-12-2017'), 'designation' => 'Exo 2017', 'boucle' => 1],
            ['id' => 2, 'annee' => 2018, 'debut' => Carbon::parse('01-01-2018'), 'fin' => Carbon::parse('31-12-2018'), 'designation' => 'Exo 2018', 'boucle' => 0],
            ['id' => 3, 'annee' => 2019, 'debut' => Carbon::parse('01-01-2019'), 'fin' => Carbon::parse('31-12-2019'), 'designation' => 'Exo 2019', 'boucle' => 0],
            ['id' => 4, 'annee' => 2020, 'debut' => Carbon::parse('01-01-2020'), 'fin' => Carbon::parse('31-12-2020'), 'designation' => 'Exo 2020', 'boucle' => 0],
            ['id' => 5, 'annee' => 2021, 'debut' => Carbon::parse('01-01-2021'), 'fin' => Carbon::parse('31-12-2021'), 'designation' => 'Exo 2021', 'boucle' => 0],
            ['id' => 6, 'annee' => 2022, 'debut' => Carbon::parse('01-01-2022'), 'fin' => Carbon::parse('31-12-2022'), 'designation' => 'Exo 2022', 'boucle' => 0],
            ['id' => 7, 'annee' => 2023, 'debut' => Carbon::parse('01-01-2023'), 'fin' => Carbon::parse('31-12-2023'), 'designation' => 'Exo 2023', 'boucle' => 0],
            ['id' => 8, 'annee' => 2024, 'debut' => Carbon::parse('01-01-2024'), 'fin' => Carbon::parse('31-12-2024'), 'designation' => 'Exo 2024', 'boucle' => 0],
            ['id' => 9, 'annee' => 2025, 'debut' => Carbon::parse('01-01-2025'), 'fin' => Carbon::parse('31-12-2025'), 'designation' => 'Exo 2025', 'boucle' => 0]
        ]);
    }
}
