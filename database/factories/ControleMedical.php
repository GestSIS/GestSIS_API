<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Infrastructure\Models\ControleMedical;
use Faker\Generator as Faker;

$factory->define(ControleMedical::class, function (Faker $faker) {
    return [
        'designation' => 'Controle',
        'consultation' => now(),
        'validite' => now(),
        'accepter' => 1,
        'en_cours' => 1,
        'sapeur_id' => $faker->numberBetween(1,25),
        'medecin_id' => $faker->numberBetween(1,10),
        'controle_medical_type_id' => $faker->numberBetween(1,6)
    ];
});
