<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groupes = array(
            array('id' => 24, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Organisation d\'alarme', 'tri' => '1'),
            array('id' => 25, 'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe SIS', 'tri' => '2'),
            array('id' => 7000004, 'pere_id' => '25', 'type' => 0, 'no' => 93, 'designation' => 'Alarme générale', 'tri' => '3'),
            array('id' => 1, 'pere_id' => '25', 'type' => 1, 'no' => 90, 'designation' => 'SIS-Cdt', 'tri' => '4'),
            array('id' => 2, 'pere_id' => '25', 'type' => 1, 'no' => 91, 'designation' => 'SIS-HS1', 'tri' => '5'),
            array('id' => 3, 'pere_id' => '25', 'type' => 1, 'no' => 92, 'designation' => 'SIS-HS2', 'tri' => '6'),
            array('id' => 4, 'pere_id' => '25', 'type' => 1, 'no' => 95, 'designation' => 'SIS-Tonne', 'tri' => '7'),
            array('id' => 5, 'pere_id' => '25', 'type' => 1, 'no' => 96, 'designation' => 'SIS-HSPAR', 'tri' => '8'),
            array('id' => 38, 'pere_id' => '25', 'type' => 1, 'no' => 98, 'designation' => 'SIS Hydrocarbure', 'tri' => '9'),
            array('id' => 26, 'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Bassecourt', 'tri' => '10'),
            array('id' => 6, 'pere_id' => '26', 'type' => 1, 'no' => 10, 'designation' => 'Bassecourt', 'tri' => '11'),
            array('id' => 27, 'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Boécourt', 'tri' => '14'),
            array('id' => 9, 'pere_id' => '27', 'type' => 1, 'no' => 20, 'designation' => 'Boécourt', 'tri' => '15'),
            array('id' => 29, 'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Courfaivre', 'tri' => '18'),
            array('id' => 12, 'pere_id' => '29', 'type' => 1, 'no' => 30, 'designation' => 'Courfaivre', 'tri' => '19'),
            array('id' => 28, 'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Glovelier', 'tri' => '22'),
            array('id' => 15, 'pere_id' => '28', 'type' => 1, 'no' => 40, 'designation' => 'Glovelier', 'tri' => '23'),
            array('id' => 30, 'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Saulcy', 'tri' => '26'),
            array('id' => 18, 'pere_id' => '30', 'type' => 1, 'no' => 50, 'designation' => 'Saulcy', 'tri' => '27'),
            array('id' => 45, 'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Soulce - Undervelier', 'tri' => '30'),
            array('id' => 21, 'pere_id' => '45', 'type' => 1, 'no' => 60, 'designation' => 'Soulce - Undervelier', 'tri' => '31'),
            array('id' => 31, 'pere_id' => '45', 'type' => 0, 'no' => NULL, 'designation' => 'Soulce info', 'tri' => '34'),
            array('id' => 32, 'pere_id' => '45', 'type' => 0, 'no' => NULL, 'designation' => 'Undervelier info', 'tri' => '35'),
            array('id' => 50, 'pere_id' => '24', 'type' => 1, 'no' => 100, 'designation' => 'SIS Recrues (Tests Alarme)', 'tri' => '36'),
            array('id' => 3000005, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Recrues 2014', 'tri' => '37'),
            array('id' => 3000006, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Recrues 2015', 'tri' => '38'),
            array('id' => 4000000, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Recrues 2016', 'tri' => '39'),
            array('id' => 7000000, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Recrues 2017', 'tri' => '40'),
            array('id' => 33, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Administration', 'tri' => '41'),
            array('id' => 37, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Autorité de surveillance', 'tri' => '42'),
            array('id' => 35, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Commission', 'tri' => '43'),
            array('id' => 34, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Etat-Major', 'tri' => '44'),
            array('id' => 36, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Organisation', 'tri' => '45'),
            array('id' => 46, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe de travail exercices', 'tri' => '46'),
            array('id' => 3000000, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe travail ex. final', 'tri' => '47'),
            array('id' => 42, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'CT Renouvellement matériel', 'tri' => '48'),
            array('id' => 48, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'JSP-District', 'tri' => '49'),
            array('id' => 49, 'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Test WGS', 'tri' => '50'),
            array('id' => 39, 'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Pour convocation', 'tri' => '51'),
            array('id' => 43, 'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe 2', 'tri' => '52'),
            array('id' => 44, 'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe 1', 'tri' => '53'),
            array('id' => 40, 'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'PAR section 1', 'tri' => '54'),
            array('id' => 41, 'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'PAR section 2', 'tri' => '55'),
            array('id' => 7000002, 'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'PAR section 3', 'tri' => '56'),
        );

        foreach ($groupes as $groupe) {
            DB::table('groupes')->insert($groupe);
        }
    }
}
