<?php

namespace Database\Factories;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Civilite;
use App\Models\Localite;
use App\Models\Sapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class SapeurFactory extends Factory
{
    protected $model = Sapeur::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'suffixe' => '',
            'rue' => $this->faker->streetName,
            'no_rue' => $this->faker->buildingNumber,
            'date_naissance' => $this->faker->dateTimeBetween('-60years', '-18years')->format('Y-m-d'),
            'no_avs' => $this->faker->avs13,
            'profession' => $this->faker->jobTitle,
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => $this->faker->city,
            'email' => $this->faker->unique()->safeEmail,
            'actif' => 1,
            'iban' => $this->faker->iban('CH'),
            'iban_statut' => 1,
            'remarque' => $this->faker->text,
            'porteur' => 0,
            'localite_id' => Localite::inRandomOrder()->first()?->id ?? 1,
            'civilite_id' => Civilite::inRandomOrder()->first()?->id ?? 1,
            'type' => SapeurBusiness::TYPE_SAPEUR,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Sapeur $sapeur) {
            $sapeur->mutations()->create([
                'localite_id' => $sapeur->localite_id,
                'incorporation' => $this->faker->dateTimeBetween('-10years', '-1year')->format('Y-m-d'),
                'sortie' => null,
                'motif' => 'Incorporation',
            ]);
        });
    }
}
