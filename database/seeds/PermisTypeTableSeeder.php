<?php

use Illuminate\Database\Seeder;

class PermisTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permis = array(
            array('type' => 'A',),
            array('type' => 'A1',),
            array('type' => 'B',),
            array('type' => 'BE',),
            array('type' => 'B1',),
            array('type' => 'C',),
            array('type' => 'CE',),
            array('type' => 'C1',),
            array('type' => 'C1E',),
            array('type' => 'D',),
            array('type' => 'DE',),
            array('type' => 'D1',),
            array('type' => 'D1E',),
            array('type' => 'M',),
            array('type' => 'F',),
            array('type' => 'G',),
            array('type' => 'TPP',),
            array('type' => 'OACP'),
        );

        foreach ($permis as $item) {
            DB::table('permis_types')->insert($item);
        }
    }
}
