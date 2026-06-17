<?php

namespace Database\Factories;

use App\Models\Fonction;
use App\Models\FonctionSapeur;
use App\Models\Sapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class FonctionSapeurFactory extends Factory
{
    protected $model = FonctionSapeur::class;

    public function definition(): array
    {
        return [
            'sapeur_id' => Sapeur::inRandomOrder()->first()?->id ?? Sapeur::factory(),
            'fonction_id' => Fonction::inRandomOrder()->first()?->id ?? 1,
            'debut' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'fin' => null,
            'remarque' => '',
        ];
    }
}
