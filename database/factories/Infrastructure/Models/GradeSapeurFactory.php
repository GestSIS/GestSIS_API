<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\GradeSapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeSapeurFactory extends Factory
{
    protected $model = GradeSapeur::class;

    public function definition(): array
    {
        return [
            'sapeur_id' => 1,
            'grade_id' => $this->faker->numberBetween(1, 10),
            'date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'remarque' => '',
        ];
    }

    public function forSapeur(int $sapeurId): self
    {
        return $this->state(fn(array $attributes) => [
            'sapeur_id' => $sapeurId,
        ]);
    }

    public function ofGrade(int $gradeId): self
    {
        return $this->state(fn(array $attributes) => [
            'grade_id' => $gradeId,
        ]);
    }

    public function withDate(string $date): self
    {
        return $this->state(fn(array $attributes) => [
            'date' => $date,
        ]);
    }

    public function withRemarque(?string $remarque): self
    {
        return $this->state(fn(array $attributes) => [
            'remarque' => $remarque,
        ]);
    }
}
