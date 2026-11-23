<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventaireLigneFactory extends Factory
{
    protected $model = InventaireLigne::class;

    /**
     * Pièce attendue, à vérifier, par défaut.
     */
    public function definition(): array
    {
        return [
            'inventaire_emplacement_id' => InventaireEmplacement::factory(),
            'article_id' => Article::factory(),
            'origine' => 'ATTENDU',
            'statut' => null,
            'materiel_type_id' => null,
            'numero' => null,
            'designation' => $this->faker->word(),
            'compartiment' => '',
            'verifie_par_sapeur_id' => null,
            'verifie_le' => null,
            'remarque_sapeur' => null,
            'traite_par_sapeur_id' => null,
            'traite_le' => null,
            'remarque_traitement' => null,
            'decision' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(['statut' => 'PRESENT', 'verifie_le' => '2026-01-01 10:00:00']);
    }

    public function absent(): static
    {
        return $this->state(['statut' => 'ABSENT', 'verifie_le' => '2026-01-01 10:00:00']);
    }

    /**
     * Matériel trouvé en plus, sans article en stock ni type existant.
     */
    public function enPlus(): static
    {
        return $this->state([
            'origine' => 'EN_PLUS',
            'article_id' => null,
            'statut' => 'PRESENT',
            'verifie_le' => '2026-01-01 10:00:00',
        ]);
    }
}
