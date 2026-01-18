<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\Groupe;
use App\Infrastructure\Models\GroupeSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupeSapeur>
 */
class GroupeSapeurFactory extends Factory
{
    protected $model = GroupeSapeur::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sapeur_id' => Sapeur::factory(),
            'groupe_id' => $this->faker->numberBetween(1, 10),
        ];
    }

    /**
     * Indicate that the groupe is for a specific sapeur.
     */
    public function forSapeur(int $sapeurId): static
    {
        return $this->state(fn(array $attributes) => [
            'sapeur_id' => $sapeurId,
        ]);
    }

    /**
     * Indicate that the groupe has a specific ID.
     */
    public function forGroupe(int $groupeId): static
    {
        return $this->state(fn(array $attributes) => [
            'groupe_id' => $groupeId,
        ]);
    }
}
