<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\CoursSapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoursSapeurFactory extends Factory
{
    protected $model = CoursSapeur::class;

    public function definition(): array
    {
        return [
            'sapeur_id' => 1,
            'cours_id' => $this->faker->numberBetween(1, 10),
            'date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'localite_id' => 1,
            'duree' => $this->faker->randomFloat(2, 0.5, 10),
        ];
    }

    public function forSapeur(int $sapeurId): self
    {
        return $this->state(fn(array $attributes) => [
            'sapeur_id' => $sapeurId,
        ]);
    }

    public function ofCours(int $coursId): self
    {
        return $this->state(fn(array $attributes) => [
            'cours_id' => $coursId,
        ]);
    }

    public function withDate(string $date): self
    {
        return $this->state(fn(array $attributes) => [
            'date' => $date,
        ]);
    }

    public function withLocalite(int $localiteId): self
    {
        return $this->state(fn(array $attributes) => [
            'localite_id' => $localiteId,
        ]);
    }

    public function withDuree(float $duree): self
    {
        return $this->state(fn(array $attributes) => [
            'duree' => $duree,
        ]);
    }
}
