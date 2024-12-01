<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciceSapeurTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('exercice_sapeur')->insert([
            ['id' => 1, 'exercice_id' => 1, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 2, 'exercice_id' => 1, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 3, 'exercice_id' => 1, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 4, 'exercice_id' => 2, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 5, 'exercice_id' => 2, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 6, 'exercice_id' => 2, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 7, 'exercice_id' => 3, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 8, 'exercice_id' => 3, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 9, 'exercice_id' => 3, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 10, 'exercice_id' => 4, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 11, 'exercice_id' => 4, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 12, 'exercice_id' => 4, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 13, 'exercice_id' => 5, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 14, 'exercice_id' => 5, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
            ['id' => 15, 'exercice_id' => 6, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'absent' => 0, 'remplace' => 0],
        ]);
    }
}
