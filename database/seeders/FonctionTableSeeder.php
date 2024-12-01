<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FonctionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('fonctions')->insert([
            ['id' => 1, 'nom' => 'Commandant', 'abreviation' => 'Cdt', 'tri' => 100, 'cumulable' => false],
            ['id' => 2, 'nom' => 'Vice-commandant', 'abreviation' => 'RCdt', 'tri' => 98, 'cumulable' => false],
            ['id' => 3, 'nom' => 'Resp . Instruction', 'abreviation' => 'RInst', 'tri' => 90, 'cumulable' => true],
            ['id' => 4, 'nom' => 'Caissier', 'abreviation' => 'Caissier', 'tri' => 82, 'cumulable' => true],
            ['id' => 5, 'nom' => 'Fourrier', 'abreviation' => 'Four', 'tri' => 80, 'cumulable' => true],
            ['id' => 6, 'nom' => 'Resp. Matériel', 'abreviation' => 'RPAM', 'tri' => 72, 'cumulable' => true],
            ['id' => 7, 'nom' => 'Resp. PAR', 'abreviation' => 'RPAR', 'tri' => 70, 'cumulable' => true],
            ['id' => 8, 'nom' => 'Resp. Radio', 'abreviation' => 'RRadio', 'tri' => 68, 'cumulable' => true],
            ['id' => 9, 'nom' => 'Resp. Mach.', 'abreviation' => 'RMACH', 'tri' => 66, 'cumulable' => true],
            ['id' => 10, 'nom' => 'Resp. Echelle', 'abreviation' => 'REch', 'tri' => 64, 'cumulable' => true],
            ['id' => 11, 'nom' => 'Resp. Section', 'abreviation' => 'RSect', 'tri' => 62, 'cumulable' => true],
            ['id' => 12, 'nom' => 'CI2', 'abreviation' => 'CI2', 'tri' => 52, 'cumulable' => false],
            ['id' => 13, 'nom' => 'CI1', 'abreviation' => 'CI1', 'tri' => 50, 'cumulable' => false],
            ['id' => 14, 'nom' => 'CG2', 'abreviation' => 'CG2', 'tri' => 34, 'cumulable' => false],
            ['id' => 16, 'nom' => 'CG1', 'abreviation' => 'CG1', 'tri' => 30, 'cumulable' => false],
            ['id' => 17, 'nom' => 'Chef Mat', 'abreviation' => 'PAM', 'tri' => 40, 'cumulable' => true],
            ['id' => 18, 'nom' => 'Machiniste', 'abreviation' => 'Mach', 'tri' => 18, 'cumulable' => true],
            ['id' => 19, 'nom' => 'Porteur', 'abreviation' => 'PAR', 'tri' => 16, 'cumulable' => false],
            ['id' => 22, 'nom' => 'Sapeur', 'abreviation' => 'Sap', 'tri' => 10, 'cumulable' => false],
            ['id' => 23, 'nom' => 'Fourrier auxiliaire', 'abreviation' => 'Four aux', 'tri' => 75, 'cumulable' => true],
        ]);
    }
}
