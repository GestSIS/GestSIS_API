<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExcuseTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('excuse_types')->insert([
            ['id' => 2, 'designation' => 'Excuse orale uniquement', 'abreviation' => 'Or', 'amende' => 0, 'statut' => 1, 'tri' => 2],
            ['id' => 3, 'designation' => 'Professionnelle', 'abreviation' => 'Pro', 'amende' => 0, 'statut' => 1, 'tri' => 3],
            ['id' => 4, 'designation' => 'Maladie / Accident', 'abreviation' => 'Ma', 'amende' => 0, 'statut' => 1, 'tri' => 4],
            ['id' => 5, 'designation' => 'En congé', 'abreviation' => 'Con', 'amende' => 0, 'statut' => 1, 'tri' => 5],
            ['id' => 8, 'designation' => 'Militaire', 'abreviation' => 'Mil', 'amende' => 0, 'statut' => 1, 'tri' => 8],
            ['id' => 9, 'designation' => 'Fonction publique', 'abreviation' => 'Fp', 'amende' => 0, 'statut' => 1, 'tri' => 9],
        ]);
    }
}
