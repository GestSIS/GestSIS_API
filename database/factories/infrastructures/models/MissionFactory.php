<?php

namespace Database\Factories\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Infrastructure\Models\Mission;

class MissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mission::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeThisYear();
        $dateTwo = clone $date;
        $dateTwo = $dateTwo->add(new DateInterval('P1D'));

        return [
            'intervention_id' => 1,
            'debut' => $date->format('Y-m-d H:i'),
            'fin' => $dateTwo->format('Y-m-d H:i'),
            'titre' => $this->faker->text(50),
            'resume' => $this->faker->text(200),
            
            'sapeur_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
