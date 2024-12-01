<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('mission_types')->insert([
            ['titre' => 'Alimentation tonne'],
            ['titre' => 'Aspiration'],
            ['titre' => 'Circulation'],
            ['titre' => 'Extinction'],
            ['titre' => 'Mise en place échelle'],
            ['titre' => 'Recherche de personnes'],
            ['titre' => 'Sauvetage'],
            ['titre' => 'Securisation']
        ]);
    }
}
