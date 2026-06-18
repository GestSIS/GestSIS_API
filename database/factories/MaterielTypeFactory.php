<?php

namespace Database\Factories;

use App\Models\MaterielType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterielTypeFactory extends Factory
{
    protected $model = MaterielType::class;

    public function definition(): array
    {
        return [
            'designation' => $this->faker->words(2, true),
            'materiel_categorie_id' => null,
            'prix' => '',
            'fournisseur' => '',
            'reparateur' => '',
            'a_controller' => false,
            'remarque' => '',
            'tri' => $this->faker->numberBetween(0, 100),
            'prefix' => '',
            'est_numerote' => false,
            'est_attribuable' => false,
            'est_taillee' => false,
            'est_lavable' => false,
        ];
    }
}
