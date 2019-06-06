<?php

use Illuminate\Database\Seeder;

class VehiculeInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        throw(new Exception("TODO"));


        foreach ($traitements as $item) {
            DB::table('intervention_traitement')->insert($item);
        }
    }
}
