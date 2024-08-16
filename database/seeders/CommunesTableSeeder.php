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
    public function run()
    {
        $communes = array(
            array('id' => '2', 'designation' => 'Boécourt'),
            array('id' => '5', 'designation' => 'Saulcy'),
            array('id' => '8', 'designation' => 'Courtételle'),
            array('id' => '9', 'designation' => 'Courrendlin'),
            array('id' => '10', 'designation' => 'Châtillon'),
            array('id' => '11', 'designation' => 'Rossemaison'),
            // array('id' => '12', 'designation' => 'Rebeuvelier'),
            // array('id' => '13', 'designation' => 'Vellerat'),
            array('id' => '14', 'designation' => 'Soyhières'),
            array('id' => '15', 'designation' => 'Pleigne'),
            array('id' => '16', 'designation' => 'Mettembert'),
            array('id' => '17', 'designation' => 'Bourrignon'),
            array('id' => '18', 'designation' => 'Ederswiler'),
            array('id' => '20', 'designation' => 'Movelier'),
            array('id' => '21', 'designation' => 'Develier'),
            array('id' => '22', 'designation' => 'Alle'),
            array('id' => '23', 'designation' => 'Beurnevésin'),
            array('id' => '24', 'designation' => 'Bonfol'),
            array('id' => '25', 'designation' => 'Coeuve'),
            array('id' => '26', 'designation' => 'Damphreux-Lugnez'),
            // array('id' => '27', 'designation' => 'Lugnez'),
            array('id' => '28', 'designation' => 'Vendlincourt'),
            array('id' => '29', 'designation' => 'Courroux'),
            array('id' => '30', 'designation' => 'Bure'),
            array('id' => '31', 'designation' => 'Cernier'),
            array('id' => '32', 'designation' => 'Les Hauts-Geneveys'),
            array('id' => '33', 'designation' => 'Fontainemelon'),
            array('id' => '34', 'designation' => 'Fontaines NE'),
            array('id' => '35', 'designation' => 'Chézard-St-Martin'),
            array('id' => '36', 'designation' => 'La Chaux-des-Breuleux'),
            array('id' => '37', 'designation' => 'Le Bémont JU'),
            array('id' => '38', 'designation' => 'Le Noirmont'),
            array('id' => '39', 'designation' => 'Les Bois'),
            array('id' => '40', 'designation' => 'Saignelégier'),
            array('id' => '41', 'designation' => 'Les Breuleux'),
            array('id' => '42', 'designation' => 'Muriaux'),
            array('id' => '43', 'designation' => 'Clos du Doubs'),
            array('id' => '44', 'designation' => 'Soubey'),
            array('id' => '67', 'designation' => 'Haute-Ajoie'),
            array('id' => '66', 'designation' => 'La Baroche'),
            array('id' => '47', 'designation' => 'Courchapoix'),
            array('id' => '48', 'designation' => 'Delémont'),
            array('id' => '50', 'designation' => 'Fontenais'),
            array('id' => '51', 'designation' => 'Châtelat'),
            array('id' => '52', 'designation' => 'Monible'),
            array('id' => '53', 'designation' => 'Rebévelier'),
            array('id' => '54', 'designation' => 'Saicourt'),
            array('id' => '55', 'designation' => 'Sornetan'),
            array('id' => '56', 'designation' => 'Souboz'),
            array('id' => '57', 'designation' => 'Boncourt'),
            array('id' => '58', 'designation' => 'Courchavon'),
            array('id' => '59', 'designation' => 'Basse-Allaine'),
            array('id' => '60', 'designation' => 'Val-Terbi'),
            array('id' => '61', 'designation' => 'Corban'),
            array('id' => '62', 'designation' => 'Mervelier'),
            array('id' => '63', 'designation' => 'Courgenay'),
            array('id' => '64', 'designation' => 'Cornol'),
            array('id' => '65', 'designation' => 'Haute-Sorne'),
            array('id' => '68', 'designation' => 'Montfaucon'),
            array('id' => '69', 'designation' => 'Balsthal'),
            array('id' => '70', 'designation' => 'Lajoux'),
            array('id' => '71', 'designation' => 'Les Genevez'),
            array('id' => '72', 'designation' => 'Fahy'),
            array('id' => '73', 'designation' => 'Courtedoux'),
            array('id' => '74', 'designation' => 'Les Enfers'),
            array('id' => '75', 'designation' => 'St-Brais'),
            array('id' => '76', 'designation' => 'Porrentruy'),
            array('id' => '77', 'designation' => 'Grand-Fontaine'),
        );

        foreach ($communes as $commune) {
            DB::table('communes')->insert($commune);
        }
    }
}
