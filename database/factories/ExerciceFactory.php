<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Exercice;

class ExerciceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Exercice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "exercice_comptable_id" => 4,
            "exercice_categorie_id" => $this->faker->numberBetween(1, 10),
            "designation" => $this->faker->randomElement($array = array('Exercice section', 'Exercice Porteur', 'Séance', 'Etat-Major')),
            "date" => $this->faker->dateTimeThisYear()->format('Y-m-d'),
            "heure" => $this->faker->time('H:i'),
            "lieu" => $this->faker->address,
            "communications" => $this->faker->realText(),
            "duree" => $this->faker->randomElement($array = array(90, 120, 180)),
            "statut" => 1,
            'localite_id' => $this->faker->randomElement($array = array(3, 5, 23, 44, 93)),
        ];
    }
}
