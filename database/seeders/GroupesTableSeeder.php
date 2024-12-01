<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('groupes')->insert([
            ['id' => 1, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Organisation d\'alarme', 'tri' => 1],
            ['id' => 2, 'pere_id' => 1, 'type' => 1, 'no' => 99, 'designation' => 'GAS', 'tri' => 2],
            ['id' => 3, 'pere_id' => 1, 'type' => 1, 'no' => 90, 'designation' => 'EM', 'tri' => 3],
            ['id' => 4, 'pere_id' => 1, 'type' => 1, 'no' => 91, 'designation' => 'Premier secours', 'tri' => 4],
            ['id' => 5, 'pere_id' => 1, 'type' => 1, 'no' => 92, 'designation' => '2ème intervention', 'tri' => 5],
            ['id' => 6, 'pere_id' => 1, 'type' => 1, 'no' => 93, 'designation' => '3ème intervention', 'tri' => 6],
            ['id' => 7, 'pere_id' => 1, 'type' => 1, 'no' => 94, 'designation' => 'Alarme générale', 'tri' => 7],
            ['id' => 8, 'pere_id' => 1, 'type' => 1, 'no' => 100, 'designation' => 'Recrues', 'tri' => 8],
            ['id' => 9, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Administration', 'tri' => 9],
            ['id' => 10, 'pere_id' => 9, 'type' => 0, 'no' => NULL, 'designation' => 'Autorité de surveillance', 'tri' => 10],
            ['id' => 11, 'pere_id' => 9, 'type' => 0, 'no' => NULL, 'designation' => 'Commission', 'tri' => 11],
            ['id' => 12, 'pere_id' => 9, 'type' => 0, 'no' => NULL, 'designation' => 'Etat-Major', 'tri' => 12],
        ]);
    }
}
