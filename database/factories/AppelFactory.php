<?php

/* @var $factory Factory */

use App\Infrastructure\Models\Appel;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Appel::class, function (Faker $faker, $interventionId) {
    return [
        'intervention_id' => $interventionId,
        'numero' => $faker->phoneNumber,
        'date' => $faker->dateTimeThisYear()->format('Y-m-d H:i'),
        'nom' => $faker->userName,
        'commentaire' => $faker->text
    ];
});
