<?php

/* @var $factory Factory */

use App\Infrastructure\Models\Mission;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Mission::class, function (Faker $faker, $params) {
    $date = $faker->dateTimeThisYear();
    $dateTwo = clone $date;
    $dateTwo = $dateTwo->add(new DateInterval('P1D'));

    return [
        'intervention_id' => $params['intervention_id'],
        'debut' => $date->format('Y-m-d H:i'),
        'fin' => $dateTwo->format('Y-m-d H:i'),
        'titre' => $faker->text(50),
        'resume' => $faker->text(200),

        'sapeur_id' => $faker->numberBetween(1, 10),
    ];
});
