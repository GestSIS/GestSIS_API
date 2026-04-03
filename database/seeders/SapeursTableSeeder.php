<?php

namespace Database\Seeders;

use App\Models\Mutation;
use App\Models\Sapeur;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

class SapeursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $sapeurs = Sapeur::factory()->count(25)->create();

        $factory = Factory::create();

        Mutation::insert(array_map(fn($s) => [
            'localite_id' => $s['localite_id'],
            'incorporation' => $factory->dateTimeBetween('-10years', '+1year'),
            'sapeur_id' => $s['id'],
            'motif' => '',
        ], $sapeurs->toArray()));

        DB::table('permis')->insert([
            ['permis_type_id' => 6, 'sapeur_id' => 2, 'date' => Carbon::parse('1958-01-01')],
            ['permis_type_id' => 2, 'sapeur_id' => 1, 'date' => Carbon::parse('1958-01-01')],
            ['permis_type_id' => 3, 'sapeur_id' => 1, 'date' => Carbon::parse('1958-01-01')]
        ]);

        DB::table('sapeur_telephone')->insert([
            ['sapeur_id' => 1, 'numero' => '032 453 34 67', 'priorite' => 1, 'telephone_type_id' => 1, 'rta' => false],
            ['sapeur_id' => 1, 'numero' => '078 752 14 25', 'priorite' => 2, 'telephone_type_id' => 2, 'rta' => true]
        ]);

        DB::table('fonction_sapeur')->insert([
            ['sapeur_id' => 1, 'fonction_id' => 5, 'debut' => Carbon::parse('2010-01-28'), 'fin' => null, 'remarque' => '']
        ]);

        DB::table('grade_sapeur')->insert([
            ['sapeur_id' => 1, 'grade_id' => 1, 'date' => Carbon::parse('2010-01-28'), 'remarque' => ''],
        ]);

        DB::table('cours_sapeur')->insert([
            ['sapeur_id' => 1, 'cours_id' => 1, 'duree' => 1, 'date' => Carbon::parse('2010-01-28'), 'localite_id' => 6],
            ['sapeur_id' => 1, 'cours_id' => 1, 'duree' => 1, 'date' => Carbon::parse('2010-01-28'), 'localite_id' => 6]
        ]);

        DB::table('groupe_sapeur')->insert([
            ['id' => 1, 'groupe_id' => 1, 'sapeur_id' => 1],
            ['id' => 2, 'groupe_id' => 5, 'sapeur_id' => 1],
            ['id' => 3, 'groupe_id' => 6, 'sapeur_id' => 1],
            ['id' => 4, 'groupe_id' => 2, 'sapeur_id' => 2],
            ['id' => 5, 'groupe_id' => 5, 'sapeur_id' => 2],
            ['id' => 6, 'groupe_id' => 4, 'sapeur_id' => 2],
            ['id' => 7, 'groupe_id' => 7, 'sapeur_id' => 2],
            ['id' => 8, 'groupe_id' => 8, 'sapeur_id' => 3],
            ['id' => 9, 'groupe_id' => 9, 'sapeur_id' => 3],
            ['id' => 10, 'groupe_id' => 10, 'sapeur_id' => 3],
            ['id' => 11, 'groupe_id' => 6, 'sapeur_id' => 3],
            ['id' => 12, 'groupe_id' => 5, 'sapeur_id' => 3],
            ['id' => 13, 'groupe_id' => 4, 'sapeur_id' => 3],
            ['id' => 14, 'groupe_id' => 11, 'sapeur_id' => 3],
        ]);

        DB::table('articles')->insert([
            ['materiel_type_id' => 1, 'sapeur_id' => 1, 'uuid' => '2387667wd661', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 2, 'sapeur_id' => 1, 'uuid' => '2387667wd662', 'numero' => 'X12389734854', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => true, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 3, 'sapeur_id' => 1, 'uuid' => '2387667wd663', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 4, 'sapeur_id' => 1, 'uuid' => '2387667wd664', 'numero' => 'X12389734855', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => true, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 5, 'sapeur_id' => 1, 'uuid' => '2387667wd665', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 6, 'sapeur_id' => 1, 'uuid' => '2387667wd666', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 7, 'sapeur_id' => 1, 'uuid' => '2387667wd667', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 8, 'sapeur_id' => 1, 'uuid' => '2387667wd668', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 9, 'sapeur_id' => 1, 'uuid' => '2387667wd669', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 10, 'sapeur_id' => 1, 'uuid' => '2387667wd6610', 'numero' => '', 'taille' => '', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 11, 'sapeur_id' => 1, 'uuid' => '2387667wd6611', 'numero' => '', 'taille' => '49', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 12, 'sapeur_id' => 1, 'uuid' => '2387667wd6612', 'numero' => '', 'taille' => '', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 13, 'sapeur_id' => 1, 'uuid' => '2387667wd6613', 'numero' => '', 'taille' => '', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 14, 'sapeur_id' => 1, 'uuid' => '2387667wd6614', 'numero' => '', 'taille' => '', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 15, 'sapeur_id' => 1, 'uuid' => '2387667wd6615', 'numero' => '', 'taille' => '', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 16, 'sapeur_id' => 1, 'uuid' => '2387667wd6616', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 17, 'sapeur_id' => 1, 'uuid' => '2387667wd6617', 'numero' => '', 'taille' => 'XL', 'remarque' => '', 'emplacement_id' => null, 'est_etiquete' => false, 'attribution' => '2014-01-01', 'retour' => null, 'achat' => '2011', 'created_at' => '2025-01-01'],
        ]);
    }
}
