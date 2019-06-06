<?php

use Illuminate\Database\Seeder;

class MaterielInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $materiels = array('TODO');

        foreach ($materiels as $item) {
            DB::table('materiels')->insert($item);
        }
    }
}
