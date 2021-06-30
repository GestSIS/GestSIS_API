<?php

namespace Database\Factories\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Infrastructure\Models\Appel;

class AppelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'intervention_id' => 1,
            'numero' => $this->faker->phoneNumber,
            'date' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i'),
            'nom' => $this->faker->userName,
            'commentaire' => $this->faker->text
        ];
    }
}