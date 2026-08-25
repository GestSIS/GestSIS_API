<?php

namespace Database\Factories;

use App\Models\Jalon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jalon>
 */
class JalonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'intervention_id' => 1,
            'titre' => $this->faker->text(50),
            'description' => $this->faker->text(200),
            'date_time' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i'),
        ];
    }
}
