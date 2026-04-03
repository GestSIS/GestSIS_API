<?php

namespace Database\Factories;

use App\Models\CoursSapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoursSapeurFactory extends Factory
{
    protected $model = CoursSapeur::class;

    public function definition(): array
    {
        return [
            'sapeur_id' => 1,
            'cours_id' => $this->faker->numberBetween(1, 10),
            'date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'localite_id' => 1,
            'duree' => $this->faker->randomFloat(2, 0.5, 10),
        ];
    }
}
