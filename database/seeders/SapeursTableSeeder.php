<?php

namespace Database\Seeders;

use App\Infrastructure\Models\Mutation;
use App\Infrastructure\Models\Sapeur;
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
            'loalite_id' => $s->localite_id,
            'incroporation' => $factory->dateTimeBetween('-10years', '+1year'),
            'sapeur_id' => $s->id,
        ], $sapeurs->toArray()));

        // permis
        DB::table('permis')->insert([
            ['permis_type_id' => 6, 'sapeur_id' => 2, 'date' => Carbon::parse('1958-01-01')],
            ['permis_type_id' => 2, 'sapeur_id' => 1, 'date' => Carbon::parse('1958-01-01')],
            ['permis_type_id' => 3, 'sapeur_id' => 1, 'date' => Carbon::parse('1958-01-01')]
        ]);

        //Telephones
        DB::table('sapeur_telephone')->insert([
            ['sapeur_id' => 1, 'numero' => '032 453 34 67', 'priorite' => 1, 'telephone_type_id' => 1, 'rta' => false],
            ['sapeur_id' => 1, 'priorite' => 2, 'numero' => '078 752 14 25', 'telephone_type_id' => 2, 'rta' => true]
        ]);

        DB::table('fonction_sapeur')->insert([
            ['sapeur_id' => 1, 'fonction_id' => 5, 'debut' => Carbon::parse('2010-01-28'), 'fin' => null, 'remarque' => '']
        ]);

        DB::table('grade_sapeur')->insert([
            ['sapeur_id' => 1, 'grade_id' => 1, 'date' => Carbon::parse('2010-01-28'), 'remarque' => ''],
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
    }
}
