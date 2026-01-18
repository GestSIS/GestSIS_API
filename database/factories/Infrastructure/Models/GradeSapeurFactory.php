<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\GradeSapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeSapeurFactory extends Factory
{
    protected $model = GradeSapeur::class;

    public function definition(): array
    {
        return [
            'sapeur_id' => 1,
            'grade_id' => $this->faker->numberBetween(1, 10),
            'date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'remarque' => '',
        ];
    }
}
