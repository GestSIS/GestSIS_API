<?php

use Illuminate\Database\Seeder;

class MaterielTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $materiels = array(
            array('id' => '1', 'designation' => 'Pompes à immersion', 'tri' => 1, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '2', 'designation' => 'Pompes à grand débit Feresta', 'tri' => 2, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '3', 'designation' => 'Aspirateurs', 'tri' => 3, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '4', 'designation' => 'Motopompe type 1', 'tri' => 4, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '5', 'designation' => 'Motopompe type 2', 'tri' => 5, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6', 'designation' => 'Ventilateur', 'tri' => 6, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '1000000', 'designation' => 'Produit absorbant ( nombre de sacs)', 'tri' => 0, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '1000001', 'designation' => 'Génératrice 220 W', 'tri' => 7, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '3000000', 'designation' => 'Seau pompe', 'tri' => 17, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000000', 'designation' => 'Caméra thermique', 'tri' => 8, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000001', 'designation' => 'Remorque inondation', 'tri' => 9, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000002', 'designation' => 'Appareil photo', 'tri' => 10, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000003', 'designation' => 'Extincteur CO2', 'tri' => 11, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000004', 'designation' => 'Extincteur mousse', 'tri' => 12, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000005', 'designation' => 'Extincteur poudre', 'tri' => 13, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000006', 'designation' => 'Couverture', 'tri' => 0, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000007', 'designation' => 'Absorbant Ecosorb', 'tri' => 14, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000008', 'designation' => 'Absorbant Oil Dry', 'tri' => 15, 'status' => 1, 'forfait' => 0, 'unite' => 1),
            array('id' => '6000009', 'designation' => 'Remorque hydrocarbure', 'tri' => 16, 'status' => 1, 'forfait' => 0, 'unite' => 1)
        );

        foreach ($materiels as $item) {
            DB::table('materiels')->insert($item);
        }
    }
}
