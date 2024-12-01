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
    public function run(): void
    {
        DB::table('amendes')->insert([
            ['id' => 1, 'ordre' => 1, 'montant' => 20, 'compte_id' => 9, 'ecriture_categorie_id' => 9],
            ['id' => 2, 'ordre' => 2, 'montant' => 50, 'compte_id' => 9, 'ecriture_categorie_id' => 9],
            ['id' => 3, 'ordre' => 3, 'montant' => 100, 'compte_id' => 9, 'ecriture_categorie_id' => 9],
        ]);
    }
}
