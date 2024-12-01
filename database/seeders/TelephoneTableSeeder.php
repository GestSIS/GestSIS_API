<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TelephoneTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('telephones')->insert([
            ['id' => '4', 'numero' => '032 420 68 04', 'nom' => 'RTA', 'tri' => '1'],
            ['id' => '5', 'numero' => '117', 'nom' => 'Police', 'tri' => '2'],
            ['id' => '6', 'numero' => '118', 'nom' => 'Feu', 'tri' => '3'],
            ['id' => '7', 'numero' => '114', 'nom' => 'Ambulance', 'tri' => '4'],
            ['id' => '8', 'numero' => '1414', 'nom' => 'Rega', 'tri' => '5'],
            ['id' => '9', 'numero' => '032 421 21 21', 'nom' => 'Hôpital', 'tri' => '6'],
            ['id' => '10', 'numero' => '032 420 21 21', 'nom' => 'Protection de la population', 'tri' => '7'],
            ['id' => '11', 'numero' => '145', 'nom' => 'Centre suiss de Toxicologie', 'tri' => '8'],
            ['id' => '12', 'numero' => '051 224 24 24', 'nom' => 'CFF Urgence', 'tri' => '13'],
            ['id' => '13', 'numero' => '061 284 81 11', 'nom' => 'Institut tropical suisse, Basel', 'tri' => '15'],
            ['id' => '14', 'numero' => '080 030 00 33', 'nom' => 'Médecin de garde', 'tri' => '16'],
            ['id' => '15', 'numero' => '032 420 51 32', 'nom' => 'Médecin cantonal', 'tri' => '17'],
            ['id' => '16', 'numero' => '032 420 73 00', 'nom' => 'Ponts et chaussées', 'tri' => '23'],
            ['id' => '17', 'numero' => '175', 'nom' => 'Swisscom', 'tri' => '24'],
            ['id' => '18', 'numero' => '032 420 52 80', 'nom' => 'Vétérinaire Cantonal', 'tri' => '25'],
            ['id' => '19', 'numero' => '032 421 36 36', 'nom' => 'Clinique Vétérinaire', 'tri' => '26'],
            ['id' => '20', 'numero' => '032 420 51 32', 'nom' => 'Office de l\'environnement', 'tri' => '30'],
            ['id' => '21', 'numero' => '117', 'nom' => 'Chimiste Cantonal', 'tri' => '11']
        ]);
    }
}
