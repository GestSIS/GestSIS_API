<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\GradeSapeur;
use App\Models\Sapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeSapeurFactory extends Factory
{
    protected $model = GradeSapeur::class;

    public function definition(): array
    {
        return [
            'sapeur_id' => Sapeur::inRandomOrder()->first()?->id ?? Sapeur::factory(),
            'grade_id' => Grade::inRandomOrder()->first()?->id ?? 1,
            'date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'remarque' => '',
        ];
    }
}
