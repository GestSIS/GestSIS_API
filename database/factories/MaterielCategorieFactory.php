<?php

namespace Database\Factories;

use App\Models\Couleur;
use App\Models\MaterielCategorie;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterielCategorieFactory extends Factory
{
    protected $model = MaterielCategorie::class;

    public function definition(): array
    {
        return [
            'designation' => $this->faker->words(2, true),
            'parent_id' => null,
            'couleur_id' => Couleur::factory(),
            'tri' => $this->faker->unique()->numberBetween(1, 1000000),
        ];
    }
}
