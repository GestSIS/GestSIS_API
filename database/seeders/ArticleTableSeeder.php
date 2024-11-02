<?php

namespace Database\Seeders;

use App\Infrastructure\Models\MaterielGenerique;
use App\Infrastructure\Models\MaterielNominal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $articles = array(
            array('id' => 1, 'achat' => '2011', 'uuid' => 'unique-1', 'numero' => '1', 'taille' => 'XL', 'remarque' => '', 'sapeur_id' => 1, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => '2022-01-12', 'materiel_type_id' => 2),
            array('id' => 2, 'achat' => '2012', 'uuid' => 'unique-2', 'numero' => '2', 'taille' => 'XS', 'remarque' => '', 'sapeur_id' => 1, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 4),
            array('id' => 3, 'achat' => '2013', 'uuid' => 'unique-3', 'numero' => '3', 'taille' => 'L', 'remarque' => '', 'sapeur_id' => 2, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 5),
            array('id' => 4, 'achat' => '2014', 'uuid' => 'unique-4', 'numero' => '4', 'taille' => '', 'remarque' => '', 'sapeur_id' => null, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 14),
            array('id' => 5, 'achat' => '2015', 'uuid' => 'unique-5', 'numero' => '5', 'taille' => 'S', 'remarque' => '', 'sapeur_id' => null, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 2),
            array('id' => 6,  'achat' => '', 'uuid' => 'unique-6', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'sapeur_id' => 1, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1),
            array('id' => 7,  'achat' => '', 'uuid' => 'unique-7', 'numero' => '', 'taille' => 'XS', 'remarque' => '', 'sapeur_id' => 2, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 3),
            array('id' => 8,  'achat' => '', 'uuid' => 'unique-8', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'sapeur_id' => 3, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1),
            array('id' => 9,  'achat' => '', 'uuid' => 'unique-9', 'numero' => '', 'taille' => 'M', 'remarque' => '', 'sapeur_id' => null, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 1),
            array('id' => 10, 'achat' => '', 'uuid' => 'unique-10', 'numero' => '', 'taille' => 'L', 'remarque' => '', 'sapeur_id' => null, 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 17),
        );
        DB::table('articles')->insert($articles);
    }
}
