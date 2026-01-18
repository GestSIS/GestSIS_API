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
            'permis_type_id' => $this->faker->numberBetween(1, 10), // Adjust range based on your permis_types
            'date' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * Indicate that the permis is for a specific sapeur.
     */
    public function forSapeur(int $sapeurId): static
    {
        return $this->state(fn(array $attributes) => [
            'sapeur_id' => $sapeurId,
        ]);
    }

    /**
     * Indicate that the permis is of a specific type.
     */
    public function ofType(int $permisTypeId): static
    {
        return $this->state(fn(array $attributes) => [
            'permis_type_id' => $permisTypeId,
        ]);
    }

    /**
     * Indicate that the permis has a specific date.
     */
    public function withDate(string $date): static
    {
        return $this->state(fn(array $attributes) => [
            'date' => $date,
        ]);
    }
}
