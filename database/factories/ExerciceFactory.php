<?php

/* @var $factory Factory */

use App\Models\Exercice;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Exercice::class, function (Faker $faker) {
    return [
        "exercice_comptable_id" => 1,
        "exercice_categorie_id" => $faker->numberBetween(1, 11),
        "designation" => $faker->randomElement($array = array('Exercice section', 'Exercice Porteur', 'Séance')),
        "date" => substr($faker->dateTimeThisYear, 10),
        "heure" => $faker->time('H:i'),
        "lieu" => $faker->address,
        "communications" => $faker->realText(),
        "duree" => $faker->randomElement($array = array(90, 120, 180)),
        "status" => 1,
        'localite_id' => $faker->numberBetween(1, 146),
    ];
});

