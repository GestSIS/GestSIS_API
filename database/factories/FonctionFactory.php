<?php

namespace Database\Factories;

use App\Models\Fonction;
use Illuminate\Database\Eloquent\Factories\Factory;

class FonctionFactory extends Factory
{
    protected $model = Fonction::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->words(2, true),
            'abreviation' => strtoupper($this->faker->lexify('???')),
            'tri' => $this->faker->numberBetween(1, 100),
            'cumulable' => $this->faker->boolean(),
            'actif' => true,
        ];
    }
}
