<?php

namespace Database\Factories;

use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventaireEmplacementFactory extends Factory
{
    protected $model = InventaireEmplacement::class;

    /**
     * Nœud racine par défaut (sans parent).
     */
    public function definition(): array
    {
        return [
            'inventaire_id' => Inventaire::factory(),
            'parent_id' => null,
            'emplacement_id' => Emplacement::factory(),
            'designation' => $this->faker->word(),
            'tri' => 0,
        ];
    }

    /**
     * Nœud enfant du nœud donné, dans le même inventaire.
     */
    public function enfantDe(InventaireEmplacement $parent): static
    {
        return $this->state([
            'inventaire_id' => $parent->inventaire_id,
            'parent_id' => $parent->id,
        ]);
    }
}
