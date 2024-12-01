<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterventionTraitementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('intervention_traitements')->insert([
            ['id' => '1', 'designation' => '-', 'tri' => '1'],
            ['id' => '2', 'designation' => 'A facturer', 'tri' => '2'],
            ['id' => '3', 'designation' => 'A vérifier', 'tri' => '3'],
            ['id' => '4', 'designation' => 'Attendre rapport police', 'tri' => '4'],
            ['id' => '5', 'designation' => 'Facturée', 'tri' => '5'],
            ['id' => '6', 'designation' => 'Payée', 'tri' => '6']
        ]);
    }
}
