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
    public function run()
    {
        $presences = array(
            array('id' => 1, 'exercice_id' => 1, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 2, 'exercice_id' => 1, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 3, 'exercice_id' => 1, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4, 'exercice_id' => 2, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 5, 'exercice_id' => 2, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 6, 'exercice_id' => 2, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 7, 'exercice_id' => 3, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 8, 'exercice_id' => 3, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 9, 'exercice_id' => 3, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 10, 'exercice_id' => 4, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 11, 'exercice_id' => 4, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 12, 'exercice_id' => 4, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 13, 'exercice_id' => 5, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 14, 'exercice_id' => 5, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 15, 'exercice_id' => 6, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
        );

        foreach ($presences as $presence) {
            DB::table('exercice_sapeur')->insert($presence);
        }
    }
}
