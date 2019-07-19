<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SapeursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Infrastructure\Models\Sapeur::class, 25)->create();

        // permis
        DB::table('permis')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'permis_type_id' => '6',
            'sapeur_id' => '2',
            'date' => Carbon::parse('1958-01-01'),
        ]);
        DB::table('permis')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'permis_type_id' => '2',
            'sapeur_id' => '1',
            'date' => Carbon::parse('1958-01-01'),
        ]);
        DB::table('permis')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'permis_type_id' => '3',
            'sapeur_id' => '1',
            'date' => Carbon::parse('1958-01-01'),
        ]);

        //Telephones
        DB::table('sapeur_telephone')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'numero' => '032 453 34 67',
            'priorite' => 1,
            'telephone_type_id' => 1,
            'rta' => false
        ]);
        DB::table('sapeur_telephone')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'priorite' => 2,
            'numero' => '078 752 14 25',
            'telephone_type_id' => 2,
            'rta' => true
        ]);

        DB::table('mutations')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'localite_id' => 5,
            'incorporation' => Carbon::parse('1998-01-01'),
            'sortie' => Carbon::parse('2000-05-05'),
            'motif' => 'Déménagement',
        ]);

        DB::table('mutations')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'localite_id' => 1,
            'incorporation' => Carbon::parse('2000-05-06'),
            'sortie' => NULL,
            'motif' => '',
        ]);

        DB::table('mutations')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 2,
            'localite_id' => 1,
            'incorporation' => Carbon::parse('2000-01-01'),
            'sortie' => NULL,
            'motif' => '',
        ]);

        DB::table('mutations')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 3,
            'localite_id' => 2,
            'incorporation' => Carbon::parse('2010-01-01'),
            'sortie' => NULL,
            'motif' => '',
        ]);

        DB::table('fonction_sapeur')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'fonction_id' => 5,
            'debut' => Carbon::parse('2010-01-28'),
            'fin' => null,
            'remarque' => ''
        ]);

        DB::table('grade_sapeur')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'grade_id' => 1,
            'date' => Carbon::parse('2010-01-28'),
            'remarque' => '',
        ]);

        DB::table('cours_sapeur')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'cours_id' => 1,
            'date' => Carbon::parse('2010-01-28'),
            'localite_id' => 6,
        ]);

        DB::table('cours_sapeur')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'cours_id' => 1,
            'date' => Carbon::parse('2010-01-28'),
            'localite_id' => 6,
        ]);

        $groupes_sapeurs = array(
            array('id' => '4','groupe_id' => '1','sapeur_id' => '1'),
            array('id' => '14','groupe_id' => '5','sapeur_id' => '1'),
            array('id' => '16','groupe_id' => '6','sapeur_id' => '1'),
            array('id' => '22','groupe_id' => '2','sapeur_id' => '2'),
            array('id' => '23','groupe_id' => '5','sapeur_id' => '2'),
            array('id' => '28','groupe_id' => '4','sapeur_id' => '2'),
            array('id' => '38','groupe_id' => '34','sapeur_id' => '2'),
            array('id' => '40','groupe_id' => '34','sapeur_id' => '3'),
            array('id' => '42','groupe_id' => '35','sapeur_id' => '3'),
            array('id' => '45','groupe_id' => '36','sapeur_id' => '3'),
            array('id' => '48','groupe_id' => '6','sapeur_id' => '3'),
            array('id' => '50','groupe_id' => '5','sapeur_id' => '3'),
            array('id' => '51','groupe_id' => '4','sapeur_id' => '3'),
            array('id' => '56','groupe_id' => '37','sapeur_id' => '3'),
        );

        foreach($groupes_sapeurs as $groupes_sapeur){
            DB::table('groupe_sapeur')->insert($groupes_sapeur);
        }
    }
}
