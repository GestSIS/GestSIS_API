<?php

/* @var $factory Factory */

use App\Infrastructure\Models\ExerciceSapeur;
use Faker\Generator as Faker;

$factory->define(ExerciceSapeur::class, function (Faker $faker) {
    return [
        "exercice_comptable_id" => 4,
        "exercice_categorie_id" => $faker->numberBetween(1, 11),
        "designation" => $faker->randomElement($array = array('Exercice section', 'Exercice Porteur', 'Séance', 'Etat-Major')),
        "date" => $faker->dateTimeThisYear()->format('d.m.Y'),
        "heure" => $faker->time('H:i'),
        "lieu" => $faker->address,
        "communications" => $faker->realText(),
        "duree" => $faker->randomElement($array = array(90, 120, 180)),
        "statut" => 1,
        'localite_id' => $faker->randomElement($array = array(3,5,23,44,93)),
    ];
});

