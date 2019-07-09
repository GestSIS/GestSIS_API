<?php

/* @var $factory Factory */

use App\Models\InterventionMateriel;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(InterventionMateriel::class, function (Faker $faker, $interventionId) {
    return [
        'intervention_id' => $interventionId,
        'quantite' => $faker->numberBetween(1, 12),
        'materiel_id' => $faker->numberBetween(1, 5),
    ];
});
