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
            'nom' => Str::random(10),
            'prenom' => Str::random(10),
            'suffixe' => '',
            'rue' => Str::random(7),
            'no_rue' => 12,
            'date_naissance' => Carbon::parse('1958-01-01'),
            'no_avs' => '756.5634.1212.12',
            'profession' => 'Artisan',
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => 'Delémont',
             
            'tel_portable' => '032 442 21 21',
            'tel_prive' => '032 442 21 32',
            'tel_professionnel' => '032 442 21 43',
            'email' => Str::random(10).'@gmail.com',
            'actif' => 1,

            'tel_portable_rta' => 1,
            'tel_prive_rta' => 1,
            'tel_proffesionnel_rta' => 0,
            'tel_portable_prio' => 0,
            'tel_prive_prio' => 1,
            'tel_proffesionnel_prio' => 1,

            'iban' => 'CH65 82000 53636 75756 7',
            'iban_status' => 1,
            'remarque' => 'Diverses remarques',
            'porteur' => 1,
            'localite_id' => 1,

        ]);
        DB::table('sapeurs')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'id' => '2',
            'nom' => Str::random(10),
            'prenom' => Str::random(10),
            'suffixe' => '',
            'rue' => Str::random(7),
            'no_rue' => 12,
            'date_naissance' => Carbon::parse('1958-01-01'),
            'no_avs' => '756.5634.1212.12',
            'profession' => 'Artisan',
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => 'Delémont',
             
            'tel_portable' => '032 442 21 21',
            'tel_prive' => '032 442 21 32',
            'tel_professionnel' => '032 442 21 43',
            'email' => Str::random(10).'@gmail.com',
            'actif' => 1,

            'tel_portable_rta' => 1,
            'tel_prive_rta' => 1,
            'tel_proffesionnel_rta' => 0,
            'tel_portable_prio' => 0,
            'tel_prive_prio' => 1,
            'tel_proffesionnel_prio' => 1,

            'iban' => 'CH65 82000 53636 75756 7',
            'iban_status' => 1,
            'remarque' => 'Diverses remarques',
            'porteur' => 1,
            'localite_id' => 1,
        ]);
        DB::table('sapeurs')->insert([
            'created_at' => now(),
            'updated_at' => now(),

            'id' => '3',
            'nom' => Str::random(10),
            'prenom' => Str::random(10),
            'suffixe' => '',
            'rue' => Str::random(7),
            'no_rue' => 12,
            'date_naissance' => Carbon::parse('1958-01-01'),
            'no_avs' => '756.5634.1212.12',
            'profession' => 'Artisan',
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => 'Delémont',
             
            'tel_portable' => '032 442 21 21',
            'tel_prive' => '032 442 21 32',
            'tel_professionnel' => '032 442 21 43',
            'email' => Str::random(10).'@gmail.com',
            'actif' => 1,

            'tel_portable_rta' => 1,
            'tel_prive_rta' => 1,
            'tel_proffesionnel_rta' => 0,
            'tel_portable_prio' => 0,
            'tel_prive_prio' => 1,
            'tel_proffesionnel_prio' => 1,

            'iban' => 'CH65 82000 53636 75756 7',
            'iban_status' => 1,
            'remarque' => 'Diverses remarques',
            'porteur' => 0,
            'localite_id' => 2,
        ]);
    }
}
