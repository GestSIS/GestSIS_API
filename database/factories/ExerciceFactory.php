<?php

namespace Database\Factories;

use App\Models\Exercice;
use App\Models\ExerciceCategorie;
use App\Models\ExerciceComptable;
use App\Models\Localite;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciceFactory extends Factory
{
    protected $model = Exercice::class;

    public function definition(): array
    {
        return [
            'exercice_comptable_id' => ExerciceComptable::inRandomOrder()->first()?->id ?? 1,
            'exercice_categorie_id' => ExerciceCategorie::inRandomOrder()->first()?->id ?? 1,
            'designation' => $this->faker->randomElement(['Exercice section', 'Exercice Porteur', 'Séance', 'Etat-Major']),
            'date' => $this->faker->dateTimeThisYear('last day of december this year')->format('Y-m-d'),
            'heure' => $this->faker->time('H:i'),
            'lieu' => $this->faker->address,
            'communications' => $this->faker->realText(),
            'duree' => $this->faker->randomElement([90, 120, 180]),
            'statut' => 1,
            'localite_id' => Localite::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}
