<?php

namespace Database\Factories;

use App\Models\Groupe;
use App\Models\GroupeSapeur;
use App\Models\Sapeur;
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
