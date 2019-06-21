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
            array('id' => '1', 'unite' => 'Franc / Heure', 'abreviation' => 'CHF / h'),
            array('id' => '2', 'unite' => 'Franc / Pièce', 'abreviation' => 'CHF / Pièce'),
            array('id' => '3', 'unite' => 'Franc', 'abreviation' => 'CHF'),
        );

        foreach ($unite as $item) {
            DB::table('type_unites')->insert($item);
        }
    }
}
