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
}
