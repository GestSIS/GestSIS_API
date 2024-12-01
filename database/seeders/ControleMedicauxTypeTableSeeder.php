<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControleMedicauxTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('controle_medical_types')->insert([
            ['id' => 1, 'designation' => 'Contrôle PAR ', 'duree_validite' => 2, 'expirable' => true, 'tri' => 1],
            ['id' => 2, 'designation' => 'Examen d\'aptitude FSS', 'duree_validite' => 2, 'expirable' => true, 'tri' => 2],
        ]);
    }
}
