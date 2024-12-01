<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $appels = array(
            array('id' => 1, 'intervention_id' => 393, 'numero' => '032 234 45 56', 'date' => '2019-12-01 12:25', 'nom' => 'Anonyme', 'commentaire' => ''),
            array('id' => 2, 'intervention_id' => 393, 'numero' => '032 234 45 56', 'date' => '2019-12-01 12:25', 'nom' => 'Anonyme', 'commentaire' => ''),
            array('id' => 3, 'intervention_id' => 393, 'numero' => '032 234 45 56', 'date' => '2019-12-01 12:25', 'nom' => 'Anonyme', 'commentaire' => ''),
        );

        foreach ($appels as $item) {
            DB::table('appels')->insert($item);
        }
    }
}
