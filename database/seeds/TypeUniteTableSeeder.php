<?php

use Illuminate\Database\Seeder;

class TypeUniteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $unite = array(
            array('id' => '1', 'unite' => 'franc/heure', 'abreviation' => 'CHF/h'),
            array('id' => '2', 'unite' => 'franc/ckm', 'abreviation' => 'CHF/km'),
            array('id' => '3', 'unite' => 'litre', 'abreviation' => 'l'),
        );

        foreach ($unite as $item) {
            DB::table('type_unites')->insert($item);
        }
    }
}
