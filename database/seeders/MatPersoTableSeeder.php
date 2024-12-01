<?php

namespace Database\Seeders;

use App\Infrastructure\Models\MaterielGenerique;
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
    public function run(): void
    {
        DB::table('materiel_personnels')->insert([
            ['id' => 1, 'taille' => 'XL', 'remarque' => '', 'sapeur_id' => 1, 'attribution' => '2014-01-01', 'retour' => '2022-01-12', 'materiel_type_id' => 2, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 1],
            ['id' => 2, 'taille' => 'XS', 'remarque' => '', 'sapeur_id' => 1, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 4, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 2],
            ['id' => 3, 'taille' => 'L', 'remarque' => '', 'sapeur_id' => 2, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 5, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 3],
            ['id' => 4, 'taille' => '', 'remarque' => '', 'sapeur_id' => null, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 14, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 4],
            ['id' => 5, 'taille' => 'S', 'remarque' => '', 'sapeur_id' => null, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 2, 'materiel_type' => MaterielNominal::class, 'materiel_id' => 5],
            ['id' => 6, 'taille' => 'XL', 'remarque' => '', 'sapeur_id' => 1, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielGenerique::class, 'materiel_id' => 1],
            ['id' => 7, 'taille' => 'XS', 'remarque' => '', 'sapeur_id' => 2, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 3, 'materiel_type' => MaterielGenerique::class, 'materiel_id' => 2],
            ['id' => 8, 'taille' => 'S', 'remarque' => '', 'sapeur_id' => 3, 'attribution' => '2014-01-01', 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielGenerique::class, 'materiel_id' => 3],
            ['id' => 9, 'taille' => 'M', 'remarque' => '', 'sapeur_id' => null, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 1, 'materiel_type' => MaterielGenerique::class, 'materiel_id' => 4],
            ['id' => 10, 'taille' => 'L', 'remarque' => '', 'sapeur_id' => null, 'attribution' => null, 'retour' => null, 'materiel_type_id' => 17, 'materiel_type' => MaterielGenerique::class, 'materiel_id' => 5],
        ]);

        DB::table('materiel_nominals')->insert([
            ['id' => 1, 'uuid' => 'nom1', 'numero' => '1', 'achat' => '2011'],
            ['id' => 2, 'uuid' => 'nom2', 'numero' => '2', 'achat' => '2012'],
            ['id' => 3, 'uuid' => 'nom3', 'numero' => '3', 'achat' => '2013'],
            ['id' => 4, 'uuid' => 'nom4', 'numero' => '4', 'achat' => '2014'],
            ['id' => 5, 'uuid' => 'nom5', 'numero' => '5', 'achat' => '2015'],
        ]);

        DB::table('materiel_generiques')->insert([
            ['id' => 1, 'quantite' => 1],
            ['id' => 2, 'quantite' => 2],
            ['id' => 3, 'quantite' => 3],
            ['id' => 4, 'quantite' => 4],
            ['id' => 5, 'quantite' => 5],
        ]);
    }
}
