<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('communes')->insert([
            ['id' => '2', 'designation' => 'Boécourt'],
            ['id' => '5', 'designation' => 'Saulcy'],
            ['id' => '8', 'designation' => 'Courtételle'],
            ['id' => '9', 'designation' => 'Courrendlin'],
            ['id' => '10', 'designation' => 'Châtillon'],
            ['id' => '11', 'designation' => 'Rossemaison'],
            // ['id' => '12', 'designation' => 'Rebeuvelier'],
            // ['id' => '13', 'designation' => 'Vellerat'],
            ['id' => '14', 'designation' => 'Soyhières'],
            ['id' => '15', 'designation' => 'Pleigne'],
            ['id' => '16', 'designation' => 'Mettembert'],
            ['id' => '17', 'designation' => 'Bourrignon'],
            ['id' => '18', 'designation' => 'Ederswiler'],
            ['id' => '20', 'designation' => 'Movelier'],
            ['id' => '21', 'designation' => 'Develier'],
            ['id' => '22', 'designation' => 'Alle'],
            ['id' => '23', 'designation' => 'Beurnevésin'],
            ['id' => '24', 'designation' => 'Bonfol'],
            ['id' => '25', 'designation' => 'Coeuve'],
            ['id' => '26', 'designation' => 'Damphreux-Lugnez'],
            // ['id' => '27', 'designation' => 'Lugnez'],
            ['id' => '28', 'designation' => 'Vendlincourt'],
            ['id' => '29', 'designation' => 'Courroux'],
            ['id' => '30', 'designation' => 'Bure'],
            ['id' => '31', 'designation' => 'Cernier'],
            ['id' => '32', 'designation' => 'Les Hauts-Geneveys'],
            ['id' => '33', 'designation' => 'Fontainemelon'],
            ['id' => '34', 'designation' => 'Fontaines NE'],
            ['id' => '35', 'designation' => 'Chézard-St-Martin'],
            ['id' => '36', 'designation' => 'La Chaux-des-Breuleux'],
            ['id' => '37', 'designation' => 'Le Bémont'],
            ['id' => '38', 'designation' => 'Le Noirmont'],
            ['id' => '39', 'designation' => 'Les Bois'],
            ['id' => '40', 'designation' => 'Saignelégier'],
            ['id' => '41', 'designation' => 'Les Breuleux'],
            ['id' => '42', 'designation' => 'Muriaux'],
            ['id' => '43', 'designation' => 'Clos du Doubs'],
            ['id' => '44', 'designation' => 'Soubey'],
            ['id' => '67', 'designation' => 'Haute-Ajoie'],
            ['id' => '66', 'designation' => 'La Baroche'],
            ['id' => '47', 'designation' => 'Courchapoix'],
            ['id' => '48', 'designation' => 'Delémont'],
            ['id' => '50', 'designation' => 'Fontenais'],
            ['id' => '51', 'designation' => 'Châtelat'],
            ['id' => '52', 'designation' => 'Monible'],
            ['id' => '53', 'designation' => 'Rebévelier'],
            ['id' => '54', 'designation' => 'Saicourt'],
            ['id' => '55', 'designation' => 'Sornetan'],
            ['id' => '56', 'designation' => 'Souboz'],
            ['id' => '57', 'designation' => 'Boncourt'],
            ['id' => '58', 'designation' => 'Courchavon'],
            ['id' => '59', 'designation' => 'Basse-Allaine'],
            ['id' => '60', 'designation' => 'Val-Terbi'],
            ['id' => '61', 'designation' => 'Corban'],
            ['id' => '62', 'designation' => 'Mervelier'],
            ['id' => '63', 'designation' => 'Courgenay'],
            ['id' => '64', 'designation' => 'Cornol'],
            ['id' => '65', 'designation' => 'Haute-Sorne'],
            ['id' => '68', 'designation' => 'Montfaucon'],
            ['id' => '69', 'designation' => 'Balsthal'],
            ['id' => '70', 'designation' => 'Lajoux'],
            ['id' => '71', 'designation' => 'Les Genevez'],
            ['id' => '72', 'designation' => 'Fahy'],
            ['id' => '73', 'designation' => 'Courtedoux'],
            ['id' => '74', 'designation' => 'Les Enfers'],
            ['id' => '75', 'designation' => 'St-Brais'],
            ['id' => '76', 'designation' => 'Porrentruy'],
            ['id' => '77', 'designation' => 'Grand-Fontaine'],
        ]);
    }
}
