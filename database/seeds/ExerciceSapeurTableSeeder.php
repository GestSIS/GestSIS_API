<?php

use Illuminate\Database\Seeder;

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
            array('id' => 4001610, 'exercice_id' => 1, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001611, 'exercice_id' => 1, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001612, 'exercice_id' => 1, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001613, 'exercice_id' => 2, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001614, 'exercice_id' => 2, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001615, 'exercice_id' => 2, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001616, 'exercice_id' => 3, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001617, 'exercice_id' => 3, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001618, 'exercice_id' => 3, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001619, 'exercice_id' => 4, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001620, 'exercice_id' => 4, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001621, 'exercice_id' => 4, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001622, 'exercice_id' => 5, 'sapeur_id' => 1, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 4001623, 'exercice_id' => 5, 'sapeur_id' => 2, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
            array('id' => 7000341, 'exercice_id' => 6, 'sapeur_id' => 3, 'excuse_type_id' => null, 'convoque' => 1, 'present' => 1, 'amende' => 0, 'remplace' => 0),
        );

        foreach ($presences as $presence) {
            DB::table('exercice_sapeur')->insert($presence);
        }
    }
}
