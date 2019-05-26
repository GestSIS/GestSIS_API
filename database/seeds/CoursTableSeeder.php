<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CoursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cours = array(
            array('id' => '1', 'precedent_id' => null, 'grade_id' => null, 'fonction_id' => '22', 'abreviation' => 'BA', 'designation' => 'Cours de base', 'Tri' => '10', 'validite_debut' => null, 'validite_fin' => Carbon::createFromDate(2014, 12, 31)),
            array('id' => '2', 'precedent_id' => '1', 'grade_id' => null, 'fonction_id' => '19', 'abreviation' => 'PAR', 'designation' => 'Porteur', 'Tri' => '65', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '7', 'precedent_id' => null, 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'Cdt', 'designation' => 'Commandant', 'Tri' => '100', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '10', 'precedent_id' => null, 'grade_id' => '4', 'fonction_id' => '5', 'abreviation' => 'Four', 'designation' => 'Fourrier', 'Tri' => '45', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '11', 'precedent_id' => '1', 'grade_id' => null, 'fonction_id' => '20', 'abreviation' => 'ELEC', 'designation' => 'Electricien', 'Tri' => '70', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '12', 'precedent_id' => '1', 'grade_id' => null, 'fonction_id' => '21', 'abreviation' => 'GC', 'designation' => 'Garde et circulation', 'Tri' => '50', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '15', 'precedent_id' => '2', 'grade_id' => null, 'fonction_id' => '7', 'abreviation' => 'PAR', 'designation' => 'Préposé app respiratoire', 'Tri' => '66', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '16', 'precedent_id' => null, 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'BLS', 'designation' => 'BLS-AED', 'Tri' => '41', 'validite_debut' => Carbon::createFromDate(2015, 1, 1), 'validite_fin' => null),
            array('id' => '17', 'precedent_id' => '16', 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'BA 1', 'designation' => 'FGB/PR (formation  général de base+PR)', 'Tri' => '42', 'validite_debut' => Carbon::createFromDate(2015, 1, 1), 'validite_fin' => null),
            array('id' => '18', 'precedent_id' => '17', 'grade_id' => null, 'fonction_id' => '19', 'abreviation' => 'BA 2', 'designation' => 'FTB (formation technique de base)', 'Tri' => '43', 'validite_debut' => Carbon::createFromDate(2015, 1, 1), 'validite_fin' => null),
            array('id' => '3', 'precedent_id' => '18', 'grade_id' => '7', 'fonction_id' => '16', 'abreviation' => 'CG 1', 'designation' => 'Chef de groupe 1 --> 2016', 'Tri' => '11', 'validite_debut' => null, 'validite_fin' => Carbon::createFromDate(2016, 12, 31)),
            array('id' => '4', 'precedent_id' => '3', 'grade_id' => '6', 'fonction_id' => '14', 'abreviation' => 'CG 2', 'designation' => 'Chef de groupe 2 --> 2016', 'Tri' => '12', 'validite_debut' => null, 'validite_fin' => Carbon::createFromDate(2016, 12, 31)),
            array('id' => '5', 'precedent_id' => '4', 'grade_id' => '5', 'fonction_id' => '13', 'abreviation' => 'CI 1', 'designation' => 'Chef d\'intervention 1', 'Tri' => '90', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '6', 'precedent_id' => '5', 'grade_id' => '3', 'fonction_id' => '12', 'abreviation' => 'CI 2', 'designation' => 'Chef d\'intervention 2', 'Tri' => '95', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '8', 'precedent_id' => '3', 'grade_id' => null, 'fonction_id' => '15', 'abreviation' => 'Ech rem', 'designation' => 'CG échelles remorquables', 'Tri' => '80', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '9', 'precedent_id' => '18', 'grade_id' => null, 'fonction_id' => '18', 'abreviation' => 'MACH', 'designation' => 'Machiniste', 'Tri' => '60', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '13', 'precedent_id' => '18', 'grade_id' => null, 'fonction_id' => '17', 'abreviation' => 'PAM', 'designation' => 'Préposé aux matériel', 'Tri' => '55', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '14', 'precedent_id' => '5', 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'DCH', 'designation' => 'Défense hydrocarbure', 'Tri' => '92', 'validite_debut' => null, 'validite_fin' => null),
            array('id' => '19', 'precedent_id' => null, 'grade_id' => '6', 'fonction_id' => '14', 'abreviation' => 'CG', 'designation' => 'Chef de groupe', 'Tri' => '85', 'validite_debut' => Carbon::createFromDate(2017, 1, 1), 'validite_fin' => null),
            array('id' => '20', 'precedent_id' => null, 'grade_id' => null, 'fonction_id' => null, 'abreviation' => 'PR', 'designation' => 'Rattrapage', 'Tri' => '45', 'validite_debut' => null, 'validite_fin' => null),
        );

        foreach ($cours as $cours_item) {
            DB::table('cours')->insert($cours_item);
        }
    }
}
