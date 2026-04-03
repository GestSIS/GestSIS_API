<?php

namespace Database\Factories;

use App\Models\FonctionSapeur;
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
}
