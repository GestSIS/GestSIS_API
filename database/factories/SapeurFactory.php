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
            'iban' => $this->faker->iban('CH'),
            'iban_statut' => 1,
            'remarque' => $this->faker->text,
            'porteur' => 0,
            'localite_id' => Localite::inRandomOrder()->first()?->id ?? 1,
            'civilite_id' => Civilite::inRandomOrder()->first()?->id ?? 1,
            'statut_sapeur' => SapeurBusiness::STATUT_ACTIF,
            'statut_civil' => SapeurBusiness::STATUT_JAMAIS,
        ];
    }

    public function civil(): static
    {
        return $this->state([
            'statut_sapeur' => SapeurBusiness::STATUT_JAMAIS,
            'statut_civil' => SapeurBusiness::STATUT_ACTIF,
        ]);
    }

    /** Ancien sapeur : mutation sapeur close */
    public function inactif(): static
    {
        return $this->state([
            'statut_sapeur' => SapeurBusiness::STATUT_INACTIF,
        ]);
    }

    /** Ancien sapeur resté civil après sa fin de service */
    public function ancienSapeurCivil(): static
    {
        return $this->state([
            'statut_sapeur' => SapeurBusiness::STATUT_INACTIF,
            'statut_civil' => SapeurBusiness::STATUT_ACTIF,
        ]);
    }

    public function recrue(): static
    {
        return $this->state([
            'statut_sapeur' => SapeurBusiness::STATUT_JAMAIS,
            'statut_civil' => SapeurBusiness::STATUT_JAMAIS,
        ]);
    }

    public function configure(): static
    {
        // Une mutation par statut renseigné, ouverte si actif, close l'an passé sinon
        return $this->afterCreating(function (Sapeur $sapeur) {
            $natures = [
                SapeurBusiness::NATURE_SAPEUR => $sapeur->statut_sapeur,
                SapeurBusiness::NATURE_CIVIL => $sapeur->statut_civil,
            ];
            foreach ($natures as $nature => $statut) {
                if ($statut === SapeurBusiness::STATUT_JAMAIS) {
                    continue;
                }
                $sapeur->mutations()->create([
                    'nature' => $nature,
                    'localite_id' => $sapeur->localite_id,
                    'incorporation' => $this->faker->dateTimeBetween('-10years', '-2year')->format('Y-m-d'),
                    'sortie' => $statut === SapeurBusiness::STATUT_ACTIF ? null : now()->subYear()->format('Y-m-d'),
                    'motif' => 'Incorporation',
                ]);
            }
        });
    }
}
