<?php

namespace Database\Factories\Infrastructure\Models;

use App\Domaine\Business\SapeurBusiness;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Infrastructure\Models\Sapeur;

class SapeurFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sapeur::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'suffixe' => '',
            'rue' => $this->faker->streetName,
            'no_rue' => $this->faker->streetSuffix,
            'date_naissance' => $this->faker->dateTimeBetween('-60years', '-10years'),
            'no_avs' => $this->faker->avs13,
            'profession' => $this->faker->jobTitle,
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => $this->faker->city,

            'email' => $this->faker->email,
            'actif' => 1,

            'iban' => $this->faker->iban('CH'),
            'iban_statut' => 1,
            'remarque' => $this->faker->text,
            'porteur' => 0,
            'localite_id' => $this->faker->numberBetween(1, 10),
            'civilite_id' => $this->faker->numberBetween(1, 2),
            // 'incorporation' => "29.01.2019",

            'type' => SapeurBusiness::TYPE_SAPEUR,
        ];
    }
}
