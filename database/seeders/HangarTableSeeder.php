<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HangarTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('hangars')->insert([
            ['id' => 1, 'designation' => 'Bassecourt', 'statut' => true, 'localite_id' => 3, 'rue' => 'Colonel Hoffmeyer', 'no_rue' => '45'],
            ['id' => 2, 'designation' => 'Glovelier', 'statut' => true, 'localite_id' => 44, 'rue' => 'Rue de Saulcy', 'no_rue' => ''],
            ['id' => 3, 'designation' => 'Courfaivre', 'statut' => true, 'localite_id' => 23, 'rue' => '', 'no_rue' => ''],
            ['id' => 4, 'designation' => 'Boécourt', 'statut' => true, 'localite_id' => 5, 'rue' => '', 'no_rue' => ''],
        ]);
    }
}
