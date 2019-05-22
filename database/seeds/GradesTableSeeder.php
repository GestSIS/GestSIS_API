<?php

use Illuminate\Database\Seeder;

class GradesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $Grade = array(
            array('GRA_ID' => '1','GRA_Designation' => 'Capitaine','GRA_Abr' => 'Cap','GRA_Grp' => '1','GRA_Tri' => '98'),
            array('GRA_ID' => '2','GRA_Designation' => 'Premier-Lieutenant','GRA_Abr' => 'Plt','GRA_Grp' => '1','GRA_Tri' => '95'),
            array('GRA_ID' => '3','GRA_Designation' => 'Lieutenant','GRA_Abr' => 'Lt','GRA_Grp' => '1','GRA_Tri' => '90'),
            array('GRA_ID' => '4','GRA_Designation' => 'Fourrier','GRA_Abr' => 'Four','GRA_Grp' => '2','GRA_Tri' => '85'),
            array('GRA_ID' => '5','GRA_Designation' => 'Sergent','GRA_Abr' => 'Sgt','GRA_Grp' => '2','GRA_Tri' => '80'),
            array('GRA_ID' => '6','GRA_Designation' => 'Caporal','GRA_Abr' => 'Cpl','GRA_Grp' => '2','GRA_Tri' => '75'),
            array('GRA_ID' => '7','GRA_Designation' => 'Appointé','GRA_Abr' => 'App','GRA_Grp' => '3','GRA_Tri' => '70'),
            array('GRA_ID' => '8','GRA_Designation' => 'Sergent-major','GRA_Abr' => 'Sgtm','GRA_Grp' => '2','GRA_Tri' => '82'),
            array('GRA_ID' => '9','GRA_Designation' => 'Major','GRA_Abr' => 'Maj','GRA_Grp' => '1','GRA_Tri' => '99'),
            array('GRA_ID' => '10','GRA_Designation' => 'Soldat','GRA_Abr' => 'Sdt','GRA_Grp' => '3','GRA_Tri' => '65'),
            array('GRA_ID' => '11','GRA_Designation' => 'Candidats','GRA_Abr' => 'Cand','GRA_Grp' => '3','GRA_Tri' => '60')
        );
    }
}
