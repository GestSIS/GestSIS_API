<?php

namespace Database\Factories\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Infrastructure\Models\InterventionMateriel;

class MaterielFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InterventionMateriel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'intervention_id' => 1,
            'quantite' => $this->faker->numberBetween(1, 12),
            'materiel_id' => $this->faker->numberBetween(1, 5),
        ];
    }
}
