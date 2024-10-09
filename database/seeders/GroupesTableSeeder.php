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
        $groupes = [
            ['id' => 1, 'parent_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Organisation d\'alarme', 'tri' => 1],
            ['id' => 2, 'parent_id' => 1, 'type' => 1, 'no' => 99, 'designation' => 'GAS', 'tri' => 2],
            ['id' => 3, 'parent_id' => 1, 'type' => 1, 'no' => 90, 'designation' => 'EM', 'tri' => 3],
            ['id' => 4, 'parent_id' => 1, 'type' => 1, 'no' => 91, 'designation' => 'Premier secours', 'tri' => 4],
            ['id' => 5, 'parent_id' => 1, 'type' => 1, 'no' => 92, 'designation' => '2ème intervention', 'tri' => 5],
            ['id' => 6, 'parent_id' => 1, 'type' => 1, 'no' => 93, 'designation' => '3ème intervention', 'tri' => 6],
            ['id' => 7, 'parent_id' => 1, 'type' => 1, 'no' => 94, 'designation' => 'Alarme générale', 'tri' => 7],
            ['id' => 8, 'parent_id' => 1, 'type' => 1, 'no' => 100, 'designation' => 'Recrues', 'tri' => 8],
            ['id' => 9, 'parent_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Administration', 'tri' => 9],
            ['id' => 10, 'parent_id' => 9, 'type' => 0, 'no' => NULL, 'designation' => 'Autorité de surveillance', 'tri' => 10],
            ['id' => 11, 'parent_id' => 9, 'type' => 0, 'no' => NULL, 'designation' => 'Commission', 'tri' => 11],
            ['id' => 12, 'parent_id' => 9, 'type' => 0, 'no' => NULL, 'designation' => 'Etat-Major', 'tri' => 12],
        ];

        DB::table('groupes')->insert($groupes);
    }
}
