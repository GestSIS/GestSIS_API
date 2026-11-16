<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleAttribution;
use App\Models\Sapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleAttributionFactory extends Factory
{
    protected $model = ArticleAttribution::class;

    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'sapeur_id' => Sapeur::factory(),
            'debut' => '2026-01-01',
            'fin' => null,
            'emplacement_retour_id' => null,
            'attribue_par_sapeur_id' => null,
            'cloture_par_sapeur_id' => null,
        ];
    }

    /**
     * Attribution en cours (état par défaut).
     */
    public function ouverte(): static
    {
        return $this->state(['fin' => null]);
    }
}
