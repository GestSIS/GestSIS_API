<?php

use Illuminate\Database\Seeder;

class FonctionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fonctions = array(
            array('id' => '1', 'nom' => 'Commandant', 'abreviation' => 'Cdt', 'tri' => 100, 'cumulable' => false),
            array('id' => '2', 'nom' => 'Vice-commandant', 'abreviation' => 'RCdt', 'tri' => 98, 'cumulable' => false),
            array('id' => '3', 'nom' => 'Resp . Instruction', 'abreviation' => 'RInst', 'tri' => 90, 'cumulable' => true),
            array('id' => '4', 'nom' => 'Caissier', 'abreviation' => 'Caissier', 'tri' => 82, 'cumulable' => true),
            array('id' => '5', 'nom' => 'Fourrier', 'abreviation' => 'Four', 'tri' => 80, 'cumulable' => true),
            array('id' => '6', 'nom' => 'Resp. Matériel', 'abreviation' => 'RPAM', 'tri' => 72, 'cumulable' => true),
            array('id' => '7', 'nom' => 'Resp. PAR', 'abreviation' => 'RPAR', 'tri' => 70, 'cumulable' => true),
            array('id' => '8', 'nom' => 'Resp. Radio', 'abreviation' => 'RRadio', 'tri' => 68, 'cumulable' => true),
            array('id' => '9', 'nom' => 'Resp. Mach.', 'abreviation' => 'RMACH', 'tri' => 66, 'cumulable' => true),
            array('id' => '10', 'nom' => 'Resp. Echelle', 'abreviation' => 'REch', 'tri' => 64, 'cumulable' => true),
            array('id' => '11', 'nom' => 'Resp. Section', 'abreviation' => 'RSect', 'tri' => 62, 'cumulable' => true),
            array('id' => '12', 'nom' => 'CI2', 'abreviation' => 'CI2', 'tri' => 52, 'cumulable' => false),
            array('id' => '13', 'nom' => 'CI1', 'abreviation' => 'CI1', 'tri' => 50, 'cumulable' => false),
            array('id' => '14', 'nom' => 'CG2', 'abreviation' => 'CG2', 'tri' => 34, 'cumulable' => false),
            array('id' => '15', 'nom' => 'CGER', 'abreviation' => 'CGER', 'tri' => 32, 'cumulable' => true),
            array('id' => '16', 'nom' => 'CG1', 'abreviation' => 'CG1', 'tri' => 30, 'cumulable' => false),
            array('id' => '17', 'nom' => 'Chef Mat', 'abreviation' => 'PAM', 'tri' => 40, 'cumulable' => true),
            array('id' => '18', 'nom' => 'Machiniste', 'abreviation' => 'Mach', 'tri' => 18, 'cumulable' => true),
            array('id' => '19', 'nom' => 'Porteur', 'abreviation' => 'PAR', 'tri' => 16, 'cumulable' => false),
            array('id' => '20', 'nom' => 'Electricien', 'abreviation' => 'ELEC', 'tri' => 14, 'cumulable' => true),
            array('id' => '21', 'nom' => 'Garde et circulation', 'abreviation' => 'GC', 'tri' => 12, 'cumulable' => false),
            array('id' => '22', 'nom' => 'Sapeur', 'abreviation' => 'Sap', 'tri' => 10, 'cumulable' => false),
            array('id' => '23', 'nom' => 'Fourrier auxiliaire', 'abreviation' => 'Four aux', 'tri' => 60, 'cumulable' => true),
            array('id' => '24', 'nom' => 'Sanitaire', 'abreviation' => 'SAN', 'tri' => 11, 'cumulable' => true),
        );

        foreach ($fonctions as $fonction) {
            DB::table('fonctions')->insert($fonction);
        }
    }
}
