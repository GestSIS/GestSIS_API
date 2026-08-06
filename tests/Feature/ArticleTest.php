<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Emplacement;
use App\Models\MaterielType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreateArticlesReturnsCreatedArticles(): void
    {
        $type = MaterielType::factory()->create(['est_numerote' => false]);
        $emplacement = Emplacement::factory()->create();

        $response = $this->json('POST', '/api/v2/articles', [
            'articles' => [
                [
                    'materiel_type_id' => $type->id,
                    'quantite' => 2,
                    'emplacement_id' => $emplacement->id,
                ],
            ],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['*' => ['id', 'materiel_type_id', 'emplacement_id']]]);
        $this->assertCount(2, $response->json('data'));
        $this->assertDatabaseHas('articles', [
            'id' => $response->json('data.0.id'),
            'materiel_type_id' => $type->id,
            'emplacement_id' => $emplacement->id,
        ]);
    }

    public function testUpdateArticlesReturnsUpdatedArticles(): void
    {
        $type = MaterielType::factory()->create(['est_numerote' => false]);
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => $emplacement->id,
            'remarque' => 'avant',
        ]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [
                [
                    'id' => $article->id,
                    'emplacement_id' => $emplacement->id,
                    'sapeur_id' => null,
                    'remarque' => 'apres',
                ],
            ],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['*' => ['id', 'remarque']]]);
        $this->assertSame('apres', $response->json('data.0.remarque'));
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'remarque' => 'apres']);
    }

    public function testStoreEmplacementArticlesRetournesLeMateriel(): void
    {
        $type = MaterielType::factory()->create();
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'sapeur_id' => null,
            'emplacement_id' => null,
        ]);

        $response = $this->json('POST', "/api/v2/emplacements/{$emplacement->id}/articles", [
            'date' => '2026-06-18',
            'articleIds' => [$article->id],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'emplacement_id' => $emplacement->id,
            'sapeur_id' => null,
            'retour' => '2026-06-18',
        ]);
    }
}
