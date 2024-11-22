<?php

namespace Database\Factories\Infrastructure\Models;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Infrastructure\Models\Medecin;

class MedecinFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Medecin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'designation' => $this->faker->firstName . ' ' . $this->faker->lastName,
            'adresse' => $this->faker->streetName,
            'actif' => 1,
            'localite_id' => $this->faker->numberBetween(1, 5),
        ];
    }
}
