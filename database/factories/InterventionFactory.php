<?php

/* @var $factory Factory */

use App\Infrastructure\Models\Intervention;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Intervention::class, function (Faker $faker) {
    $date = $faker->dateTimeThisYear();
    $dateTwo = clone $date;
    $dateTwo = $dateTwo->add(new DateInterval('P1D'));

    return [
        'date_debut' => $date->format('d.m.Y'),
        'heure_debut' => $date->format('H:m'),
        'lieu' => $faker->streetAddress,
        'objet' => $faker->randomElement(['Inondation', 'Incendie']),
        'date_fin' => $dateTwo->format('d.m.Y'),
        'heure_fin' => $dateTwo->format('H:m'),
        'rapport_police' => 0,
        'degre' => $faker->numberBetween(1, 3),
        'sauve_personne' => $faker->numberBetween(0, 5),
        'sauve_animaux' => $faker->numberBetween(0, 2),
        'description' => $faker->text,
        'proprietaire' => $faker->name . '\n' . $faker->address,
        'responsable' => $faker->name . '\n' . $faker->address,
        'stat_nb' => 1,
        'statut' => 0,
        'exercice_comptable_id' => 3,
        'localite_id' => $faker->numberBetween(1, 146),
        'type_intervention_id' => $faker->numberBetween(1, 5),
        'sapeur_id' => $faker->numberBetween(1, 10),
        'stat_federal_id' => $faker->numberBetween(1, 5),
        'intervention_traitement_id' => 1,
        'date_imputation' => null
    ];
});

