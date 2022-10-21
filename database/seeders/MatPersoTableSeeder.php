<?php

namespace Database\Seeders;

use App\Infrastructure\Models\MaterielIndiscernable;
use App\Infrastructure\Models\MaterielNominal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MatPersoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $materiels = array(
            array('id' => 1, 'taille' => 'XL', 'remarque' => '', 'sapeur_id' => 1, 'attribution' => '2014-01-01', 'retour' => '2022-01-12', 'materiel_type_id' => 1, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 1),
            array('id' => 2, 'taille' => 'XS', 'remarque' => '', 'sapeur_id' => 1, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 2),
            array('id' => 3, 'taille' => 'L', 'remarque' => '', 'sapeur_id' => 2, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 3),
            array('id' => 4, 'taille' => 'M', 'remarque' => '', 'sapeur_id' => null, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 4),
            array('id' => 5, 'taille' => 'S', 'remarque' => '', 'sapeur_id' => null, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 5),
            array('id' => 6, 'taille' => 'XL', 'remarque' => '', 'sapeur_id' => 1, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielIndiscernable::class, 'materiel_id' => 1),
            array('id' => 7, 'taille' => 'XS', 'remarque' => '', 'sapeur_id' => 2, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielIndiscernable::class, 'materiel_id' => 2),
            array('id' => 8, 'taille' => 'S', 'remarque' => '', 'sapeur_id' => 3, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielIndiscernable::class, 'materiel_id' => 3),
            array('id' => 9, 'taille' => 'M', 'remarque' => '', 'sapeur_id' => null, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielIndiscernable::class, 'materiel_id' => 4),
            array('id' => 10, 'taille' => 'L', 'remarque' => '', 'sapeur_id' => null, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielIndiscernable::class, 'materiel_id' => 5),
        );
        DB::table('materiel_personnels')->insert($materiels);

        $nominal = array(
            array('id' => 1, 'uuid' => 'nom1', 'numero' => '1', 'achat' => '2011'),
            array('id' => 2, 'uuid' => 'nom2', 'numero' => '2', 'achat' => '2012'),
            array('id' => 3, 'uuid' => 'nom3', 'numero' => '3', 'achat' => '2013'),
            array('id' => 4, 'uuid' => 'nom4', 'numero' => '4', 'achat' => '2014'),
            array('id' => 5, 'uuid' => 'nom5', 'numero' => '5', 'achat' => '2015'),
        );
        DB::table('materiel_nominals')->insert($nominal);

        $indiscernable = array(
            array('id' => 1, 'quantite' => 1),
            array('id' => 2, 'quantite' => 2),
            array('id' => 3, 'quantite' => 3),
            array('id' => 4, 'quantite' => 4),
            array('id' => 5, 'quantite' => 5),
        );
        DB::table('materiel_indiscernables')->insert($indiscernable);
    }
}
