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
            array('id' => 24, 'actif' => 1,'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Organisation d\'alarme', 'info' => '', 'tri' => '1'),
            array('id' => 25, 'actif' => 1,'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe SIS', 'info' => '', 'tri' => '2'),
            array('id' => 7000004, 'actif' => 1,'pere_id' => '25', 'type' => 0, 'no' => '93', 'designation' => 'Alarme générale', 'info' => '', 'tri' => '3'),
            array('id' => 1, 'actif' => 1,'pere_id' => '25', 'type' => 1, 'no' => '90', 'designation' => 'SIS-Cdt', 'info' => '', 'tri' => '4'),
            array('id' => 2, 'actif' => 1,'pere_id' => '25', 'type' => 1, 'no' => '91', 'designation' => 'SIS-HS1', 'info' => '', 'tri' => '5'),
            array('id' => 3, 'actif' => 1,'pere_id' => '25', 'type' => 1, 'no' => '92', 'designation' => 'SIS-HS2', 'info' => '', 'tri' => '6'),
            array('id' => 4, 'actif' => 1,'pere_id' => '25', 'type' => 1, 'no' => '95', 'designation' => 'SIS-Tonne', 'info' => '', 'tri' => '7'),
            array('id' => 5, 'actif' => 1,'pere_id' => '25', 'type' => 1, 'no' => '96', 'designation' => 'SIS-HSPAR', 'info' => '', 'tri' => '8'),
            array('id' => 38, 'actif' => 1,'pere_id' => '25', 'type' => 1, 'no' => '98', 'designation' => 'SIS Hydrocarbure', 'info' => '', 'tri' => '9'),
            array('id' => 26, 'actif' => 1,'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Bassecourt', 'info' => '', 'tri' => '10'),
            array('id' => 6, 'actif' => 1,'pere_id' => '26', 'type' => 1, 'no' => '10', 'designation' => 'Bassecourt', 'info' => '', 'tri' => '11'),
            array('id' => 27, 'actif' => 1,'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Boécourt', 'info' => '', 'tri' => '14'),
            array('id' => 9, 'actif' => 1,'pere_id' => '27', 'type' => 1, 'no' => '20', 'designation' => 'Boécourt', 'info' => '', 'tri' => '15'),
            array('id' => 29, 'actif' => 1,'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Courfaivre', 'info' => '', 'tri' => '18'),
            array('id' => 12, 'actif' => 1,'pere_id' => '29', 'type' => 1, 'no' => '30', 'designation' => 'Courfaivre', 'info' => '', 'tri' => '19'),
            array('id' => 28, 'actif' => 1,'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Glovelier', 'info' => '', 'tri' => '22'),
            array('id' => 15, 'actif' => 1,'pere_id' => '28', 'type' => 1, 'no' => '40', 'designation' => 'Glovelier', 'info' => '', 'tri' => '23'),
            array('id' => 30, 'actif' => 1,'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Saulcy', 'info' => '', 'tri' => '26'),
            array('id' => 18, 'actif' => 1,'pere_id' => '30', 'type' => 1, 'no' => '50', 'designation' => 'Saulcy', 'info' => '', 'tri' => '27'),
            array('id' => 45, 'actif' => 1,'pere_id' => '24', 'type' => 0, 'no' => NULL, 'designation' => 'Soulce - Undervelier', 'info' => '', 'tri' => '30'),
            array('id' => 21, 'actif' => 1,'pere_id' => '45', 'type' => 1, 'no' => '60', 'designation' => 'Soulce - Undervelier', 'info' => '', 'tri' => '31'),
            array('id' => 31, 'actif' => 1,'pere_id' => '45', 'type' => 0, 'no' => NULL, 'designation' => 'Soulce info', 'info' => '', 'tri' => '34'),
            array('id' => 32, 'actif' => 1,'pere_id' => '45', 'type' => 0, 'no' => NULL, 'designation' => 'Undervelier info', 'info' => '', 'tri' => '35'),
            array('id' => 50, 'actif' => 1,'pere_id' => '24', 'type' => 1, 'no' => '100', 'designation' => 'SIS Recrues (Tests Alarme)', 'info' => '', 'tri' => '36'),
            array('id' => 3000005, 'actif' => 1,'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Recrues 2014', 'info' => '', 'tri' => '37'),
            array('id' => 3000006, 'actif' => 1,'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Recrues 2015', 'info' => '', 'tri' => '38'),
            array('id' => 4000000, 'actif' => 1,'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Recrues 2016', 'info' => '', 'tri' => '39'),
            array('id' => 7000000, 'actif' => 1,'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Recrues 2017', 'info' => '', 'tri' => '40'),
            array('id' => 33, 'actif' => 1,'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Administration', 'info' => '', 'tri' => '41'),
            array('id' => 37, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Autorité de surveillance', 'info' => '', 'tri' => '42'),
            array('id' => 35, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Commission', 'info' => '', 'tri' => '43'),
            array('id' => 34, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Etat-Major', 'info' => '', 'tri' => '44'),
            array('id' => 36, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Organisation', 'info' => '', 'tri' => '45'),
            array('id' => 46, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe de travail exercices', 'info' => '', 'tri' => '46'),
            array('id' => 3000000, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe travail ex. final', 'info' => '', 'tri' => '47'),
            array('id' => 42, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'CT Renouvellement matériel', 'info' => '', 'tri' => '48'),
            array('id' => 48, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'JSP-District', 'info' => '', 'tri' => '49'),
            array('id' => 49, 'actif' => 1,'pere_id' => '33', 'type' => 0, 'no' => NULL, 'designation' => 'Test WGS', 'info' => '', 'tri' => '50'),
            array('id' => 39, 'actif' => 1,'pere_id' => null, 'type' => 0, 'no' => NULL, 'designation' => 'Pour convocation', 'info' => '', 'tri' => '51'),
            array('id' => 43, 'actif' => 1,'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe 2', 'info' => '', 'tri' => '52'),
            array('id' => 44, 'actif' => 1,'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'Groupe 1', 'info' => '', 'tri' => '53'),
            array('id' => 40, 'actif' => 1,'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'PAR section 1', 'info' => '', 'tri' => '54'),
            array('id' => 41, 'actif' => 1,'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'PAR section 2', 'info' => '', 'tri' => '55'),
            array('id' => 7000002, 'actif' => 1,'pere_id' => '39', 'type' => 0, 'no' => NULL, 'designation' => 'PAR section 3', 'info' => '', 'tri' => '56'),
        );

        foreach ($groupes as $groupe) {
            DB::table('groupes')->insert($groupe);
        }
    }
}
