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
        DB::table('sapeurs')->insert([
            'created_at' => now(),
            'updated_at' => now(),
            
            'id' => '1',
            'nom' => 'Dutoit',
            'prenom' => 'Sarah',
            'suffixe' => '',
            'rue' => Str::random(7),
            'no_rue' => '12a',
            'date_naissance' => Carbon::parse('1958-01-01'),
            'no_avs' => '756.5634.1212.12',
            'profession' => 'Artisan',
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => 'Delémont',

            'email' => Str::random(10).'@gmail.com',
            'actif' => 1,

            'iban' => 'CH65 82000 53636 75756 7',
            'iban_status' => 1,
            'remarque' => 'Diverses remarques',
            'porteur' => 1,
            'localite_id' => 1,
            'civilite_id' => 1

        ]);
        DB::table('sapeurs')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'id' => '2',
            'nom' => 'Wermeille',
            'prenom' => 'Benoit',
            'suffixe' => '',
            'rue' => Str::random(7),
            'no_rue' => '12',
            'date_naissance' => Carbon::parse('1958-01-01'),
            'no_avs' => '756.5634.1212.12',
            'profession' => 'Artisan',
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => 'Delémont',

            'email' => Str::random(10).'@gmail.com',
            'actif' => 1,

            'iban' => 'CH65 82000 53636 75756 7',
            'iban_status' => 1,
            'remarque' => 'Diverses remarques',
            'porteur' => 1,
            'localite_id' => 1,
            'civilite_id' => 2
        ]);
        DB::table('sapeurs')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'id' => '3',
            'nom' => 'Desboeufs',
            'prenom' => 'Antoine',
            'suffixe' => '',
            'rue' => Str::random(7),
            'no_rue' => '123',
            'date_naissance' => Carbon::parse('1958-01-01'),
            'no_avs' => '756.5634.1212.12',
            'profession' => 'Artisan',
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => 'Delémont',

            'email' => Str::random(10).'@gmail.com',
            'actif' => 1,

            'iban' => 'CH65 82000 53636 75756 7',
            'iban_status' => 1,
            'remarque' => 'Diverses remarques',
            'porteur' => 0,
            'localite_id' => 2,
            'civilite_id' => 1
        ]);

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
        DB::table('sapeurs_telephones')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'numero' => '032 453 34 67',
            'tri' => 1,
            'telephone_type_id' => 1,
            'rta' => false
        ]);
        DB::table('sapeurs_telephones')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'sapeur_id' => 1,
            'tri' => 2,
            'numero' => '078 752 14 25',
            'telephone_type_id' => 2,
            'rta' => true
        ]);
    }
}
