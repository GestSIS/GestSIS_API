<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Infrastructure\Models\Medecin;
use Faker\Generator as Faker;

$factory->define(Medecin::class, function (Faker $faker) {
    return [
        'designation' => $faker->firstName . ' ' . $faker->lastName,
        'addresse' => $faker->streetName,
        'actif' => 1,
        'localite_id' => $faker->numberBetween(1,146),
    ];
});
