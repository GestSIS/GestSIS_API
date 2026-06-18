<?php

namespace Database\Factories;

use App\Models\Couleur;
use App\Models\Emplacement;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmplacementFactory extends Factory
{
    protected $model = Emplacement::class;

    public function definition(): array
    {
        return [
            'designation' => $this->faker->words(2, true),
            'remarque' => '',
            'tri' => $this->faker->numberBetween(0, 100),
            'est_etiquete' => false,
            'est_compartimentable' => false,
            'couleur_id' => Couleur::factory(),
            'parent_id' => null,
            'statut' => true,
        ];
    }
}
