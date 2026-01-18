<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\FonctionSapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class FonctionSapeurFactory extends Factory
{
    protected $model = FonctionSapeur::class;

    public function definition(): array
    {
        return [
            'sapeur_id' => 1,
            'fonction_id' => $this->faker->numberBetween(1, 10),
            'debut' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'fin' => null,
            'remarque' => '',
        ];
    }

    public function forSapeur(int $sapeurId): self
    {
        return $this->state(fn(array $attributes) => [
            'sapeur_id' => $sapeurId,
        ]);
    }

    public function ofFonction(int $fonctionId): self
    {
        return $this->state(fn(array $attributes) => [
            'fonction_id' => $fonctionId,
        ]);
    }

    public function withDebut(string $debut): self
    {
        return $this->state(fn(array $attributes) => [
            'debut' => $debut,
        ]);
    }

    public function withFin(?string $fin): self
    {
        return $this->state(fn(array $attributes) => [
            'fin' => $fin,
        ]);
    }

    public function withRemarque(?string $remarque): self
    {
        return $this->state(fn(array $attributes) => [
            'remarque' => $remarque,
        ]);
    }
}
