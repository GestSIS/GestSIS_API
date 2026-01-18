<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\SapeurTelephone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SapeurTelephone>
 */
class SapeurTelephoneFactory extends Factory
{
    protected $model = SapeurTelephone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sapeur_id' => Sapeur::factory(),
            'telephone_type_id' => $this->faker->numberBetween(1, 5),
            'numero' => $this->faker->phoneNumber(),
            'rta' => $this->faker->boolean(),
            'priorite' => $this->faker->numberBetween(1, 5),
        ];
    }

    /**
     * Indicate that the telephone is for a specific sapeur.
     */
    public function forSapeur(int $sapeurId): static
    {
        return $this->state(fn(array $attributes) => [
            'sapeur_id' => $sapeurId,
        ]);
    }

    /**
     * Indicate that the telephone is of a specific type.
     */
    public function ofType(int $telephoneTypeId): static
    {
        return $this->state(fn(array $attributes) => [
            'telephone_type_id' => $telephoneTypeId,
        ]);
    }

    /**
     * Indicate that the telephone has a specific number.
     */
    public function withNumero(string $numero): static
    {
        return $this->state(fn(array $attributes) => [
            'numero' => $numero,
        ]);
    }

    /**
     * Indicate that the telephone has RTA enabled.
     */
    public function withRta(bool $rta = true): static
    {
        return $this->state(fn(array $attributes) => [
            'rta' => $rta,
        ]);
    }

    /**
     * Indicate that the telephone has a specific priority.
     */
    public function withPriorite(int $priorite): static
    {
        return $this->state(fn(array $attributes) => [
            'priorite' => $priorite,
        ]);
    }
}
