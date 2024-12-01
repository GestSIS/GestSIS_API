<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('cours')->insert([
            ['id' => '1', 'duree' => '1', 'precedent_id' => null, 'grade_id' => null, 'fonction_id' => '22', 'abreviation' => 'BA', 'designation' => 'Cours de base', 'Tri' => '10', 'validite_debut' => null, 'validite_fin' => Carbon::createFromDate(2014, 12, 31)],
            ['id' => '2', 'duree' => '1', 'precedent_id' => '1', 'grade_id' => null, 'fonction_id' => '19', 'abreviation' => 'PAR', 'designation' => 'Porteur', 'Tri' => '65', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '7', 'duree' => '1', 'precedent_id' => null, 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'Cdt', 'designation' => 'Commandant', 'Tri' => '100', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '10', 'duree' => '1', 'precedent_id' => null, 'grade_id' => '4', 'fonction_id' => '5', 'abreviation' => 'Four', 'designation' => 'Fourrier', 'Tri' => '45', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '15', 'duree' => '1', 'precedent_id' => '2', 'grade_id' => null, 'fonction_id' => '7', 'abreviation' => 'PAR', 'designation' => 'Préposé app respiratoire', 'Tri' => '66', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '16', 'duree' => '1', 'precedent_id' => null, 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'BLS', 'designation' => 'BLS-AED', 'Tri' => '41', 'validite_debut' => Carbon::createFromDate(2015, 1, 1), 'validite_fin' => null],
            ['id' => '17', 'duree' => '1', 'precedent_id' => '16', 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'BA 1', 'designation' => 'FGB/PR (formation  général de base+PR)', 'Tri' => '42', 'validite_debut' => Carbon::createFromDate(2015, 1, 1), 'validite_fin' => null],
            ['id' => '18', 'duree' => '1', 'precedent_id' => '17', 'grade_id' => null, 'fonction_id' => '19', 'abreviation' => 'BA 2', 'designation' => 'FTB (formation technique de base)', 'Tri' => '43', 'validite_debut' => Carbon::createFromDate(2015, 1, 1), 'validite_fin' => null],
            ['id' => '3', 'duree' => '1', 'precedent_id' => '18', 'grade_id' => '7', 'fonction_id' => '16', 'abreviation' => 'CG 1', 'designation' => 'Chef de groupe 1 --> 2016', 'Tri' => '11', 'validite_debut' => null, 'validite_fin' => Carbon::createFromDate(2016, 12, 31)],
            ['id' => '4', 'duree' => '1', 'precedent_id' => '3', 'grade_id' => '6', 'fonction_id' => '14', 'abreviation' => 'CG 2', 'designation' => 'Chef de groupe 2 --> 2016', 'Tri' => '12', 'validite_debut' => null, 'validite_fin' => Carbon::createFromDate(2016, 12, 31)],
            ['id' => '5', 'duree' => '1', 'precedent_id' => '4', 'grade_id' => 12, 'fonction_id' => '13', 'abreviation' => 'CI 1', 'designation' => 'Chef d\'intervention 1', 'Tri' => '90', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '6', 'duree' => '1', 'precedent_id' => '5', 'grade_id' => 3, 'fonction_id' => '12', 'abreviation' => 'CI 2', 'designation' => 'Chef d\'intervention 2', 'Tri' => '95', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '8', 'duree' => '1', 'precedent_id' => '3', 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'Ech rem', 'designation' => 'CG échelles remorquables', 'Tri' => '80', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '9', 'duree' => '1', 'precedent_id' => '18', 'grade_id' => 7, 'fonction_id' => '18', 'abreviation' => 'MACH', 'designation' => 'Machiniste', 'Tri' => '60', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '13', 'duree' => '1', 'precedent_id' => '18', 'grade_id' => null, 'fonction_id' => '17', 'abreviation' => 'PAM', 'designation' => 'Préposé aux matériel', 'Tri' => '55', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '14', 'duree' => '1', 'precedent_id' => '5', 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'DCH', 'designation' => 'Défense hydrocarbure', 'Tri' => '92', 'validite_debut' => null, 'validite_fin' => null],
            ['id' => '19', 'duree' => '1', 'precedent_id' => null, 'grade_id' => 6, 'fonction_id' => '14', 'abreviation' => 'CG', 'designation' => 'Chef de groupe', 'Tri' => '85', 'validite_debut' => Carbon::createFromDate(2017, 1, 1), 'validite_fin' => null],
            ['id' => '20', 'duree' => '1', 'precedent_id' => null, 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'PR', 'designation' => 'Rattrapage', 'Tri' => '45', 'validite_debut' => null, 'validite_fin' => null],
        ]);
    }
}
