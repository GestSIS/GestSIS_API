<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\Permis;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permis>
 */
class PermisFactory extends Factory
{
    protected $model = Permis::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sapeur_id' => Sapeur::factory(),
            'permis_type_id' => $this->faker->numberBetween(1, 10),
            'date' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
        ];
    }
}
