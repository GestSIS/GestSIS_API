<?php

use Illuminate\Database\Seeder;

class MissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        throw(new Exception("TODO"));



        foreach ($materiels as $item) {
            DB::table('intervention_traitement')->insert($item);
        }
    }
}
