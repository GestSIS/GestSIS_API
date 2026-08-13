<?php

namespace Database\Factories;

use App\Models\RecrutementToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecrutementToken>
 */
class RecrutementTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => hash('sha256', $this->faker->uuid()),
            'expire_at' => now()->addHours(24),
        ];
    }
}
