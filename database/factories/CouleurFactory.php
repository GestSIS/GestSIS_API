<?php

namespace Database\Factories;

use App\Models\Couleur;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouleurFactory extends Factory
{
    protected $model = Couleur::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->colorName(),
            'texte' => $this->faker->hexColor(),
            'fond' => $this->faker->hexColor(),
        ];
    }
}
