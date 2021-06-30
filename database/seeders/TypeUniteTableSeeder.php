<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            array('id' => '1', 'unite' => 'Franc / pièce', 'abreviation' => 'CHF / pce'),
            array('id' => '2', 'unite' => 'Franc / heure', 'abreviation' => 'CHF / h'),
            array('id' => '3', 'unite' => 'Franc / an', 'abreviation' => 'CHF / an'),
            array('id' => '4', 'unite' => 'Franc / km', 'abreviation' => 'CHF / km'),
            array('id' => '5', 'unite' => 'Franc / jour', 'abreviation' => 'CHF / j'),
            array('id' => '6', 'unite' => 'Franc', 'abreviation' => 'CHF'),
        );

        foreach ($unite as $item) {
            DB::table('type_unites')->insert($item);
        }
    }
}
