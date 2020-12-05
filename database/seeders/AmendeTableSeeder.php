<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmendeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $amendes = array(
            array('id' => 1, 'order' => 1, 'montant' => 0, 'compte_id' => 9, 'ecriture_categorie_id' => 9),
            array('id' => 2, 'order' => 2, 'montant' => 50, 'compte_id' => 9, 'ecriture_categorie_id' => 9),
            array('id' => 3, 'order' => 3, 'montant' => 100, 'compte_id' => 9, 'ecriture_categorie_id' => 9),
        );

        foreach ($amendes as $item) {
            DB::table('amendes')->insert($item);
        }
    }
}
