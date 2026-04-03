<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ControleMedical;

class ControleMedicalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ControleMedical::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'designation' => 'Controle',
            'consultation' => now(),
            'validite' => now()->addYears($this->faker->numberBetween(1, 5)),
            'accepter' => 1,
            'en_cours' => 1,
            'sapeur_id' => $this->faker->numberBetween(1, 25),
            'medecin_id' => $this->faker->numberBetween(1, 10),
            'controle_medical_type_id' => $this->faker->numberBetween(1, 2)
        ];
    }
}
