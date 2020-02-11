<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Sapeur::class, function (Faker $faker) {
    return [
        'nom' => $faker->lastName,
        'prenom' => $faker->firstName,
        'suffixe' => '',
        'rue' => $faker->streetName,
        'no_rue' => $faker->streetSuffix,
        'date_naissance' => $faker->dateTimeBetween('-60years','-10years'),
        'no_avs' => $faker->avs13,
        'profession' => $faker->jobTitle,
        'employeur' => 'Canton du Jura',
        'lieu_de_travail' => $faker->city,

        'email' => $faker->email,
        'actif' => 1,

        'iban' => $faker->iban('CH'),
        'iban_statut' => 1,
        'remarque' => $faker->text,
        'porteur' => 0,
        'localite_id' => $faker->numberBetween(1,146),
        'civilite_id' => $faker->numberBetween(1,2)
    ];
});
