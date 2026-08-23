<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
use App\Models\Couleur;
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

    private function vehiculeType(array $extra = []): MaterielType
    {
        return MaterielType::factory()->create(array_merge([
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_emplacement' => true,
        ], $extra));
    }

    public function testCreateVehiculeArticleCreatesLinkedEmplacementWithoutOwnLocation(): void
    {
        $type = $this->vehiculeType();
        $parent = Emplacement::factory()->create();
        $couleur = Couleur::factory()->create();

        $response = $this->json('POST', '/api/v2/articles', [
            'articles' => [[
                'materiel_type_id' => $type->id,
                'quantite' => 1,
                'designation' => 'Camion 1',
                'remarque' => 'Tonne-pompe',
                'emplacement' => [
                    'couleur_id' => $couleur->id,
                    'parent_id' => $parent->id,
                ],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $articleId = $response->json('data.0.id');
        $this->assertDatabaseHas('articles', [
            'id' => $articleId,
            'emplacement_id' => null,
            'sapeur_id' => null,
        ]);
        $this->assertDatabaseHas('emplacements', [
            'article_id' => $articleId,
            'couleur_id' => $couleur->id,
            'parent_id' => $parent->id,
            'designation' => 'Camion 1',
            'remarque' => 'Tonne-pompe',
        ]);
    }

    public function testCreateVehiculeArticleWithoutCouleurIsRejectedAndNotPersisted(): void
    {
        $type = $this->vehiculeType();

        $response = $this->json('POST', '/api/v2/articles', [
            'articles' => [[
                'materiel_type_id' => $type->id,
                'quantite' => 1,
                'designation' => 'Camion sans couleur',
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseMissing('articles', ['designation' => 'Camion sans couleur']);
    }

    public function testEditVehiculeArticleSynchronisesLinkedEmplacement(): void
    {
        $type = $this->vehiculeType();
        $parent = Emplacement::factory()->create();
        $nouveauParent = Emplacement::factory()->create();
        $couleur = Couleur::factory()->create();
        $nouvelleCouleur = Couleur::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => null,
            'sapeur_id' => null,
            'designation' => 'Avant',
            'remarque' => 'Avant',
        ]);
        $emplacement = Emplacement::factory()->create([
            'article_id' => $article->id,
            'couleur_id' => $couleur->id,
            'parent_id' => $parent->id,
        ]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'designation' => 'Apres',
                'remarque' => 'Apres',
                'emplacement' => [
                    'couleur_id' => $nouvelleCouleur->id,
                    'parent_id' => $nouveauParent->id,
                ],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('emplacements', [
            'id' => $emplacement->id,
            'designation' => 'Apres',
            'remarque' => 'Apres',
            'couleur_id' => $nouvelleCouleur->id,
            'parent_id' => $nouveauParent->id,
        ]);
    }

    public function testChangeVehiculeArticleTypeToAnotherVehiculeSousTypeIsAllowed(): void
    {
        $type = $this->vehiculeType();
        $autreTypeVehicule = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        Emplacement::factory()->create(['article_id' => $article->id, 'couleur_id' => $couleur->id]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'materiel_type_id' => $autreTypeVehicule->id,
                'emplacement' => ['couleur_id' => $couleur->id],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'materiel_type_id' => $autreTypeVehicule->id]);
    }

    public function testChangeVehiculeArticleTypeToNonVehiculeTypeIsRejected(): void
    {
        $type = $this->vehiculeType();
        $typeStandard = MaterielType::factory()->create();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        Emplacement::factory()->create(['article_id' => $article->id, 'couleur_id' => $couleur->id]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'materiel_type_id' => $typeStandard->id,
                'emplacement' => ['couleur_id' => $couleur->id],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'materiel_type_id' => $type->id]);
    }

    public function testChangeMaterielTypeIdOnNonVehiculeArticleIsRejected(): void
    {
        $typeA = MaterielType::factory()->create();
        $typeB = MaterielType::factory()->create();
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $typeA->id, 'emplacement_id' => $emplacement->id]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'materiel_type_id' => $typeB->id,
                'emplacement_id' => $emplacement->id,
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'materiel_type_id' => $typeA->id]);
    }

    public function testDeleteVehiculeArticleRejectedWhileEmplacementContainsMateriel(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        $emplacement = Emplacement::factory()->create(['article_id' => $article->id, 'couleur_id' => $couleur->id]);
        Article::factory()->create(['emplacement_id' => $emplacement->id]);

        $response = $this->json('DELETE', '/api/v2/articles', [
            'articleIds' => [$article->id],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('articles', ['id' => $article->id]);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id]);
    }

    public function testDeleteVehiculeArticleRejectedWhileEmplacementHasSousEmplacement(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        $emplacement = Emplacement::factory()->create(['article_id' => $article->id, 'couleur_id' => $couleur->id]);
        Emplacement::factory()->create(['parent_id' => $emplacement->id]);

        $response = $this->json('DELETE', '/api/v2/articles', [
            'articleIds' => [$article->id],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('articles', ['id' => $article->id]);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id]);
    }

    public function testDeleteEmptyVehiculeArticleDeletesArticleAndLinkedEmplacement(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        $emplacement = Emplacement::factory()->create(['article_id' => $article->id, 'couleur_id' => $couleur->id]);

        $response = $this->json('DELETE', '/api/v2/articles', [
            'articleIds' => [$article->id],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertDatabaseMissing('emplacements', ['id' => $emplacement->id]);
    }

    public function testEditVehiculeArticleWithoutLinkedEmplacementIsRejectedGracefully(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        // Véhicule antérieur à la fonctionnalité : pas encore d'emplacement lié.
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'designation' => 'Apres',
                'emplacement' => ['couleur_id' => $couleur->id],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
    }

    public function testRetourArticlesRejectedForVehiculeArticle(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        Emplacement::factory()->create(['article_id' => $article->id, 'couleur_id' => $couleur->id]);
        $destination = Emplacement::factory()->create();

        $response = $this->json('POST', "/api/v2/emplacements/{$destination->id}/articles", [
            'date' => '2026-06-18',
            'articleIds' => [$article->id],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'emplacement_id' => null]);
    }
}
