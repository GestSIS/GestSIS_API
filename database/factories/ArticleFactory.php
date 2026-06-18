<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\MaterielType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'materiel_type_id' => MaterielType::factory(),
            'numero' => '',
            'uuid' => $this->faker->unique()->uuid(),
            'achat' => '',
            'taille' => '',
            'remarque' => '',
            'attribution' => null,
            'retour' => null,
            'sapeur_id' => null,
            'emplacement_id' => null,
            'compartiment' => '',
            'est_etiquete' => false,
            'est_unique' => false,
            'designation' => '',
            'immatriculation' => '',
            'chassis' => '',
            'statut' => true,
        ];
    }
}
