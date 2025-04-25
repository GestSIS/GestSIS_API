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
        DB::table('emplacements')->insert([
            ['id' => 13, 'parent_id' => null, 'designation' => 'Stock', 'est_etiquete' => true, 'tri' => 13, 'couleur_id' => 9, 'statut' => 1],
        ]);
    }
}
