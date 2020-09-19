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
    public function run()
    {
        $traitements = array(
            array('id' => '1', 'designation' => '-', 'tri' => '1'),
            array('id' => '2', 'designation' => 'A facturer', 'tri' => '2'),
            array('id' => '3', 'designation' => 'A vérifier', 'tri' => '3'),
            array('id' => '4', 'designation' => 'Attendre rapport police', 'tri' => '4'),
            array('id' => '5', 'designation' => 'Facturée', 'tri' => '5'),
            array('id' => '6', 'designation' => 'Payée', 'tri' => '6')
        );

        foreach ($traitements as $item) {
            DB::table('intervention_traitements')->insert($item);
        }
    }
}
