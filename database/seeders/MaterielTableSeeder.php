<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterielTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // TODO: à migrer
        DB::table('materiels')->insert([
            ['id' => 1, 'designation' => 'Pompes à immersion', 'tri' => 1, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 2, 'designation' => 'Pompes à grand débit Feresta', 'tri' => 2, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 3, 'designation' => 'Aspirateurs', 'tri' => 3, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 4, 'designation' => 'Motopompe type 1', 'tri' => 4, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 5, 'designation' => 'Motopompe type 2', 'tri' => 5, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 6, 'designation' => 'Ventilateur', 'tri' => 6, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 7, 'designation' => 'Produit absorbant ( nombre de sacs)', 'tri' => 18, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 8, 'designation' => 'Génératrice 220 W', 'tri' => 7, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 9, 'designation' => 'Seau pompe', 'tri' => 17, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 10, 'designation' => 'Caméra thermique', 'tri' => 8, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 11, 'designation' => 'Remorque inondation', 'tri' => 9, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 12, 'designation' => 'Appareil photo', 'tri' => 10, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 13, 'designation' => 'Extincteur CO2', 'tri' => 11, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 14, 'designation' => 'Extincteur mousse', 'tri' => 12, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 15, 'designation' => 'Extincteur poudre', 'tri' => 13, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 16, 'designation' => 'Couverture', 'tri' => 17, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 17, 'designation' => 'Absorbant Ecosorb', 'tri' => 14, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 18, 'designation' => 'Absorbant Oil Dry', 'tri' => 15, 'statut' => 1, 'forfait' => 0, 'unite' => 1],
            ['id' => 19, 'designation' => 'Remorque hydrocarbure', 'tri' => 16, 'statut' => 1, 'forfait' => 0, 'unite' => 1]
        ]);
    }
}
