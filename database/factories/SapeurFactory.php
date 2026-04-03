<?php

namespace Database\Factories;

use App\Domaine\Business\SapeurBusiness;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sapeur;

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
            'date_naissance' => $this->faker->dateTimeBetween('-60years', '-18years')->format('Y-m-d'),
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

            'type' => SapeurBusiness::TYPE_SAPEUR,
        ];
    }

    /**
     * Configure the factory to create an entry mutation after creating a sapeur.
     */
    public function configure()
    {
        return $this->afterCreating(function (Sapeur $sapeur) {
            // Create an entry mutation with incorporation date
            // Use a default date if not set (mimics the API behavior)
            $incorporationDate = $this->faker->dateTimeBetween('-10years', '-1year')->format('Y-m-d');

            $sapeur->mutations()->create([
                'localite_id' => $sapeur->localite_id,
                'incorporation' => $incorporationDate,
                'sortie' => null,
                'motif' => 'Incorporation',
            ]);
        });
    }
}
