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
}
