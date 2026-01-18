<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\Cours;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoursFactory extends Factory
{
    protected $model = Cours::class;

    public function definition(): array
    {
        return [
            'designation' => $this->faker->words(3, true),
            'abreviation' => strtoupper($this->faker->lexify('???')),
            'tri' => $this->faker->numberBetween(1, 100),
            'duree' => $this->faker->randomFloat(2, 0.5, 40),
            'validite_debut' => null,
            'validite_fin' => null,
            'fonction_id' => null,
            'grade_id' => null,
            'precedent_id' => null,
        ];
    }
}
