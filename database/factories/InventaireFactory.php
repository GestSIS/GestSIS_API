<?php

namespace Database\Factories;

use App\Models\Inventaire;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventaireFactory extends Factory
{
    protected $model = Inventaire::class;

    public function definition(): array
    {
        return [
            'designation' => 'Inventaire ' . $this->faker->word(),
            'date' => '2026-01-01',
            'statut' => 'EN_COURS',
            'remarque' => null,
            'demarre_par_sapeur_id' => null,
            'valide_par_sapeur_id' => null,
            'valide_le' => null,
        ];
    }

    /**
     * Inventaire validé par le sapeur.
     */
    public function valide(): static
    {
        return $this->state([
            'statut' => 'VALIDE',
            'valide_le' => '2026-01-01 18:00:00',
        ]);
    }
}
