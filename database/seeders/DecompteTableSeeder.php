<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DecompteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = array(
            array('id' => 1, 'designation' => 'partiel', 'exercice_comptable_id' => 4, 'deduction' => 0, 'avs_total' => 0, 'ac_total' => 0, 'total' => 0),
            array('id' => 2, 'designation' => 'final', 'exercice_comptable_id' => 4, 'deduction' => 1, 'avs_total' => 0, 'ac_total' => 0, 'total' => 0),
        );

        foreach ($categories as $categorie) {
            DB::table('decomptes')->insert($categorie);
        }
    }
}
