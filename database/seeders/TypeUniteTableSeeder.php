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
            array('id' => '1', 'comptable' => True, 'unite' => 'pièce', 'abreviation' => 'pce'),
            array('id' => '2', 'comptable' => True, 'unite' => 'heure', 'abreviation' => 'h'),
            array('id' => '3', 'comptable' => True, 'unite' => 'an', 'abreviation' => 'an'),
            array('id' => '4', 'comptable' => True, 'unite' => 'km', 'abreviation' => 'km'),
            array('id' => '5', 'comptable' => True, 'unite' => 'jour', 'abreviation' => 'j'),
            array('id' => '6', 'comptable' => False, 'unite' => 'forfait', 'abreviation' => ''),
            array('id' => '7', 'comptable' => True, 'unite' => 'mois', 'abreviation' => 'm'),
        );

        foreach ($unite as $item) {
            DB::table('type_unites')->insert($item);
        }
    }
}
