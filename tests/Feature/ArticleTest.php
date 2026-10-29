<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\Couleur;
use App\Models\Emplacement;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
                    'statut' => true,
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

    public function testCreateNumeroteArticleWithoutNumeroIsRejectedGracefully(): void
    {
        $type = MaterielType::factory()->create(['est_numerote' => true]);
        $emplacement = Emplacement::factory()->create();

        $response = $this->json('POST', '/api/v2/articles', [
            'articles' => [
                [
                    'materiel_type_id' => $type->id,
                    'quantite' => 1,
                    'emplacement_id' => $emplacement->id,
                ],
            ],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseMissing('articles', ['materiel_type_id' => $type->id]);
    }

    public function testEditNumeroteArticleWithoutNumeroIsRejectedGracefully(): void
    {
        $type = MaterielType::factory()->create(['est_numerote' => true]);
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => $emplacement->id,
            'numero' => 'ABC123',
        ]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [
                [
                    'id' => $article->id,
                    'statut' => true,
                    'emplacement_id' => $emplacement->id,
                    'numero' => null,
                ],
            ],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'numero' => 'ABC123']);
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

        $response->assertStatus(201);
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

    public function testCreateArticlesRollsBackWholeBatchWhenOneArticleIsInvalid(): void
    {
        // Un lot contient un article valide (serait créé en premier) puis un véhicule sans
        // couleur (rejeté) : la transaction doit empêcher le premier de rester en base.
        $typeStandard = MaterielType::factory()->create(['est_numerote' => false]);
        $emplacement = Emplacement::factory()->create();
        $typeVehicule = $this->vehiculeType();

        $response = $this->json('POST', '/api/v2/articles', [
            'articles' => [
                [
                    'materiel_type_id' => $typeStandard->id,
                    'quantite' => 1,
                    'emplacement_id' => $emplacement->id,
                    'designation' => 'Article valide du lot',
                ],
                [
                    'materiel_type_id' => $typeVehicule->id,
                    'quantite' => 1,
                    'designation' => 'Camion sans couleur',
                ],
            ],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseMissing('articles', ['designation' => 'Article valide du lot']);
        $this->assertDatabaseMissing('articles', ['designation' => 'Camion sans couleur']);
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

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
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
                'statut' => true,
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

    public function testEditVehiculeArticleStatutIsIndependentFromEmplacementStatut(): void
    {
        // On peut désactiver le véhicule (retiré des interventions) sans désactiver
        // son emplacement (reste visible/rangeable dans l'inventaire), et inversement.
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => null,
            'sapeur_id' => null,
            'statut' => true,
        ]);
        $emplacement = Emplacement::factory()->create([
            'article_id' => $article->id,
            'couleur_id' => $couleur->id,
            'statut' => true,
        ]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'statut' => false,
                'emplacement' => [
                    'couleur_id' => $couleur->id,
                    'statut' => true,
                ],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'statut' => false]);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id, 'statut' => true]);
    }

    public function testEditVehiculeArticleCannotDeactivateEmplacementContainingMateriel(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => null,
            'sapeur_id' => null,
        ]);
        $emplacement = Emplacement::factory()->create([
            'article_id' => $article->id,
            'couleur_id' => $couleur->id,
            'statut' => true,
        ]);
        Article::factory()->create(['emplacement_id' => $emplacement->id, 'sapeur_id' => null]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'emplacement' => [
                    'couleur_id' => $couleur->id,
                    'statut' => false,
                ],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id, 'statut' => true]);
    }

    public function testEditVehiculeArticleCanDeactivateEmptyEmplacement(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => null,
            'sapeur_id' => null,
        ]);
        $emplacement = Emplacement::factory()->create([
            'article_id' => $article->id,
            'couleur_id' => $couleur->id,
            'statut' => true,
        ]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'statut' => true,
                'emplacement' => [
                    'couleur_id' => $couleur->id,
                    'statut' => false,
                ],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id, 'statut' => false]);
    }

    public function testEditVehiculeArticleCannotSetOwnEmplacementAsItsOwnParent(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => null,
            'sapeur_id' => null,
        ]);
        $emplacement = Emplacement::factory()->create([
            'article_id' => $article->id,
            'couleur_id' => $couleur->id,
        ]);

        $response = $this->json('PUT', '/api/v2/articles', [
            'articles' => [[
                'id' => $article->id,
                'statut' => true,
                'emplacement' => [
                    'couleur_id' => $couleur->id,
                    'parent_id' => $emplacement->id,
                ],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id, 'parent_id' => null]);
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
                'statut' => true,
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
                'statut' => true,
                'materiel_type_id' => $typeStandard->id,
                'emplacement' => ['couleur_id' => $couleur->id],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
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
                'statut' => true,
                'materiel_type_id' => $typeB->id,
                'emplacement_id' => $emplacement->id,
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
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

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
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

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
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

        $response->assertStatus(204);
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertDatabaseMissing('emplacements', ['id' => $emplacement->id]);
    }

    public function testDeleteArticleRejectedWhileItHasControles(): void
    {
        $article = Article::factory()->create();
        $controle = Controle::create([
            'nom' => 'Contrôle test',
            'recurrence_type' => 'NON_PERIODIQUE',
        ]);
        ControleExec::create([
            'controle_id' => $controle->id,
            'article_id' => $article->id,
            'executed_at' => now(),
            'executed_by' => Sapeur::factory()->create()->id,
            'trigger_type' => 'NON_PERIODIQUE',
        ]);

        $response = $this->json('DELETE', '/api/v2/articles', [
            'articleIds' => [$article->id],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('articles', ['id' => $article->id]);
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
                'statut' => true,
                'designation' => 'Apres',
                'emplacement' => ['couleur_id' => $couleur->id],
            ]],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
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

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'emplacement_id' => null]);
    }

    public function testGetArticlesParEmplacementIncludesSousEmplacements(): void
    {
        $parent = Emplacement::factory()->create();
        $enfant = Emplacement::factory()->create(['parent_id' => $parent->id]);
        $petitEnfant = Emplacement::factory()->create(['parent_id' => $enfant->id]);
        $autre = Emplacement::factory()->create();

        $articleDirect = Article::factory()->create(['emplacement_id' => $parent->id, 'sapeur_id' => null]);
        $articleEnfant = Article::factory()->create(['emplacement_id' => $enfant->id, 'sapeur_id' => null]);
        $articlePetitEnfant = Article::factory()->create(['emplacement_id' => $petitEnfant->id, 'sapeur_id' => null]);
        $articleAutre = Article::factory()->create(['emplacement_id' => $autre->id, 'sapeur_id' => null]);

        $response = $this->json('GET', "/api/v2/emplacements/{$parent->id}/articles", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($articleDirect->id));
        $this->assertTrue($ids->contains($articleEnfant->id));
        $this->assertTrue($ids->contains($articlePetitEnfant->id));
        $this->assertFalse($ids->contains($articleAutre->id));
    }

    public function testGetArticlesParEmplacementForLeafEmplacementReturnsOnlyItsOwn(): void
    {
        $parent = Emplacement::factory()->create();
        $enfant = Emplacement::factory()->create(['parent_id' => $parent->id]);

        $articleParent = Article::factory()->create(['emplacement_id' => $parent->id, 'sapeur_id' => null]);
        $articleEnfant = Article::factory()->create(['emplacement_id' => $enfant->id, 'sapeur_id' => null]);

        $response = $this->json('GET', "/api/v2/emplacements/{$enfant->id}/articles", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($articleEnfant->id));
        $this->assertFalse($ids->contains($articleParent->id));
    }

    public function testGetArticlesParEmplacementIncludesVehiculeNestedAsSousEmplacement(): void
    {
        // Un véhicule ne range pas ses articles via `emplacement_id` : il possède son
        // propre emplacement (via Emplacement.article_id). Ce sous-emplacement doit
        // quand même faire apparaître le véhicule quand on consulte le hangar parent.
        $hangar = Emplacement::factory()->create();
        $vehicule = Article::factory()->create(['emplacement_id' => null, 'sapeur_id' => null]);
        Emplacement::factory()->create(['parent_id' => $hangar->id, 'article_id' => $vehicule->id]);

        $articleRange = Article::factory()->create(['emplacement_id' => $hangar->id, 'sapeur_id' => null]);

        $response = $this->json('GET', "/api/v2/emplacements/{$hangar->id}/articles", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($vehicule->id));
        $this->assertTrue($ids->contains($articleRange->id));
    }

    public function testGetArticlesParEmplacementForVehiculeOwnEmplacementExcludesItself(): void
    {
        $vehicule = Article::factory()->create(['emplacement_id' => null, 'sapeur_id' => null]);
        $emplacementVehicule = Emplacement::factory()->create(['article_id' => $vehicule->id]);
        $articleRange = Article::factory()->create(['emplacement_id' => $emplacementVehicule->id, 'sapeur_id' => null]);

        $response = $this->json('GET', "/api/v2/emplacements/{$emplacementVehicule->id}/articles", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($articleRange->id));
        $this->assertFalse($ids->contains($vehicule->id));
    }

    public function testArticlesARecupererReturnsOnlyActiveArticlesOfInactiveSapeurs(): void
    {
        $type = MaterielType::factory()->create();
        $sapeurInactif = Sapeur::factory()->create(['actif' => false]);
        $sapeurActif = Sapeur::factory()->create(['actif' => true]);

        $articleARecuperer = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'sapeur_id' => $sapeurInactif->id,
            'emplacement_id' => null,
            'statut' => true,
        ]);
        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'sapeur_id' => $sapeurActif->id,
            'emplacement_id' => null,
            'statut' => true,
        ]);
        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'sapeur_id' => $sapeurInactif->id,
            'emplacement_id' => null,
            'statut' => false,
        ]);

        $response = $this->json('GET', '/api/v2/articles/a-recuperer', [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertSame([$articleARecuperer->id], $ids->all());
        $this->assertSame($sapeurInactif->id, $response->json('data.0.sapeur.id'));
        $this->assertSame($type->designation, $response->json('data.0.materiel_type.designation'));
    }

    public function testListeArticlesPdfPourSapeurAvecLogo(): void
    {
        $sapeur = $this->creerSapeurAvecArticle();
        Storage::fake();
        config(['database.dbs' => ['test']]);
        Storage::put('documents/test/logo/logo.png', UploadedFile::fake()->image('logo.png')->getContent());

        $response = $this->get('/api/v2/sapeurs/' . $sapeur->id . '/liste-articles', ['Sis-Key' => 'test']);

        $response->assertStatus(200);
        $response->assertDownload('liste-articles.pdf');
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    public function testListeArticlesPdfPourSapeurSansLogo(): void
    {
        $sapeur = $this->creerSapeurAvecArticle();
        // Vrai disque (le logo de remplacement y est versionné), SIS sans dossier logo
        config(['database.dbs' => ['sis_sans_logo']]);

        $response = $this->get('/api/v2/sapeurs/' . $sapeur->id . '/liste-articles', ['Sis-Key' => 'sis_sans_logo']);

        $response->assertStatus(200);
        $response->assertDownload('liste-articles.pdf');
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    private function creerSapeurAvecArticle(): Sapeur
    {
        $sapeur = Sapeur::factory()->create();
        Article::factory()->create([
            'materiel_type_id' => MaterielType::factory()->create()->id,
            'sapeur_id' => $sapeur->id,
            'emplacement_id' => null,
            'attribution' => '2026-01-10',
        ]);

        return $sapeur;
    }

    public function testAttribuerArticleRemetRetourANull(): void
    {
        $sapeur = Sapeur::factory()->create();
        $type = MaterielType::factory()->create(['est_attribuable' => true]);
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'sapeur_id' => null,
            'emplacement_id' => $emplacement->id,
            'attribution' => '2025-06-01',
            'retour' => '2025-12-01',
        ]);

        $response = $this->json('POST', '/api/v2/sapeurs/' . $sapeur->id . '/articles', [
            'date' => '2026-01-10',
            'articleIds' => [$article->id],
        ], ['Sis-Key' => 1]);

        $response->assertStatus(201);
        $article->refresh();
        $this->assertSame($sapeur->id, $article->sapeur_id);
        $this->assertNull($article->retour);
    }


    public function testCreateArticleAttribueSansAttributionEstRefuse(): void
    {
        $type = MaterielType::factory()->create(['est_attribuable' => true]);

        $this->json('POST', '/api/v2/articles', ['articles' => [[
            'materiel_type_id' => $type->id,
            'quantite' => 1,
            'sapeur_id' => Sapeur::factory()->create()->id,
        ]]], ['Sis-Key' => 1])->assertStatus(422)->assertJsonValidationErrors(['articles.0.attribution']);
    }

    public function testCreateArticleAttribueSansCleEmplacementIdFonctionne(): void
    {
        $type = MaterielType::factory()->create(['est_attribuable' => true]);
        $sapeur = Sapeur::factory()->create();

        $this->json('POST', '/api/v2/articles', ['articles' => [[
            'materiel_type_id' => $type->id,
            'quantite' => 1,
            'sapeur_id' => $sapeur->id,
            'attribution' => '2026-10-29',
        ]]], ['Sis-Key' => 1])->assertStatus(200);

        $this->assertDatabaseHas('articles', ['materiel_type_id' => $type->id, 'sapeur_id' => $sapeur->id, 'emplacement_id' => null]);
    }

    public function testCreateArticleDeTypeInconnuEstRefuse(): void
    {
        $this->json('POST', '/api/v2/articles', ['articles' => [[
            'materiel_type_id' => 999999,
            'quantite' => 1,
            'emplacement_id' => Emplacement::factory()->create()->id,
        ]]], ['Sis-Key' => 1])->assertStatus(422)->assertJsonValidationErrors(['articles.0.materiel_type_id']);
    }

    public function testCreateArticlesSansListeEstRefuse(): void
    {
        $this->json('POST', '/api/v2/articles', [], ['Sis-Key' => 1])
            ->assertStatus(422)->assertJsonValidationErrors(['articles']);
    }

    public function testCreateArticlesQuantiteLimiteeA50(): void
    {
        $type = MaterielType::factory()->create(['est_numerote' => false]);
        $emplacement = Emplacement::factory()->create();
        $ligne = ['materiel_type_id' => $type->id, 'emplacement_id' => $emplacement->id];

        $this->json('POST', '/api/v2/articles', ['articles' => [[...$ligne, 'quantite' => 51]]], ['Sis-Key' => 1])
            ->assertStatus(422)->assertJsonValidationErrors(['articles.0.quantite']);

        $this->json('POST', '/api/v2/articles', ['articles' => [[...$ligne, 'quantite' => 50]]], ['Sis-Key' => 1])
            ->assertStatus(200);
        $this->assertSame(50, Article::where('materiel_type_id', $type->id)->count());
    }

    public function testUpdateArticlesSansStatutEstRefuse(): void
    {
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create(['emplacement_id' => $emplacement->id, 'sapeur_id' => null, 'statut' => false]);

        $this->json('PUT', '/api/v2/articles', ['articles' => [[
            'id' => $article->id,
            'emplacement_id' => $emplacement->id,
        ]]], ['Sis-Key' => 1])->assertStatus(422)->assertJsonValidationErrors(['articles.0.statut']);

        $this->assertFalse($article->fresh()->statut);
    }

    public function testUpdateArticleInexistantEstRefuse(): void
    {
        $this->json('PUT', '/api/v2/articles', ['articles' => [[
            'id' => 999999,
            'emplacement_id' => Emplacement::factory()->create()->id,
            'statut' => true,
        ]]], ['Sis-Key' => 1])->assertStatus(422)->assertJsonValidationErrors(['articles.0.id']);
    }

    public function testCreateArticleDansUnEmplacementInactifEstRefuse(): void
    {
        $type = MaterielType::factory()->create(['est_numerote' => false]);
        $inactif = Emplacement::factory()->create(['statut' => false]);

        $this->json('POST', '/api/v2/articles', ['articles' => [[
            'materiel_type_id' => $type->id,
            'quantite' => 1,
            'emplacement_id' => $inactif->id,
        ]]], ['Sis-Key' => 1])->assertStatus(422);

        $this->assertDatabaseMissing('articles', ['emplacement_id' => $inactif->id]);
    }

    public function testDeplacerUnArticleDansUnEmplacementInactifEstRefuse(): void
    {
        $actif = Emplacement::factory()->create();
        $inactif = Emplacement::factory()->create(['statut' => false]);
        $article = Article::factory()->create(['emplacement_id' => $actif->id, 'sapeur_id' => null]);

        $this->json('PUT', '/api/v2/articles', ['articles' => [[
            'id' => $article->id,
            'emplacement_id' => $inactif->id,
            'statut' => true,
        ]]], ['Sis-Key' => 1])->assertStatus(422);

        $this->assertSame($actif->id, $article->fresh()->emplacement_id);
    }

    public function testReenregistrerUnArticleDejaDansUnEmplacementInactifEstAccepte(): void
    {
        // Données historiques : seul un déplacement vers un emplacement inactif est refusé.
        $inactif = Emplacement::factory()->create(['statut' => false]);
        $article = Article::factory()->create(['emplacement_id' => $inactif->id, 'sapeur_id' => null]);

        $this->json('PUT', '/api/v2/articles', ['articles' => [[
            'id' => $article->id,
            'emplacement_id' => $inactif->id,
            'remarque' => 'modifiée',
            'statut' => true,
        ]]], ['Sis-Key' => 1])->assertStatus(200);
    }

    public function testRetourArticlesDansUnEmplacementInactifEstRefuse(): void
    {
        $inactif = Emplacement::factory()->create(['statut' => false]);
        $article = Article::factory()->create(['sapeur_id' => Sapeur::factory()->create()->id, 'emplacement_id' => null]);

        $this->json('POST', "/api/v2/emplacements/{$inactif->id}/articles", [
            'date' => '2026-10-29',
            'articleIds' => [$article->id],
        ], ['Sis-Key' => 1])->assertStatus(422);

        $this->assertNull($article->fresh()->emplacement_id);
    }

    public function testCreateVehiculeSousUnEmplacementParentInactifEstRefuse(): void
    {
        $type = $this->vehiculeType();
        $parentInactif = Emplacement::factory()->create(['statut' => false]);

        $this->json('POST', '/api/v2/articles', ['articles' => [[
            'materiel_type_id' => $type->id,
            'quantite' => 1,
            'numero' => 'V1',
            'designation' => 'Tonne-pompe',
            'emplacement' => ['couleur_id' => Couleur::factory()->create()->id, 'parent_id' => $parentInactif->id],
        ]]], ['Sis-Key' => 1])->assertStatus(422);

        $this->assertDatabaseMissing('emplacements', ['parent_id' => $parentInactif->id]);
    }

    public function testEditVehiculeRefuseNeModifiePasLarticle(): void
    {
        // Le refus d'un contrôle sur l'emplacement lié (ici un cycle) ne doit pas
        // laisser l'article à moitié modifié.
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => null,
            'sapeur_id' => null,
            'remarque' => 'avant',
        ]);
        $emplacement = Emplacement::factory()->create(['article_id' => $article->id, 'couleur_id' => $couleur->id]);

        $this->json('PUT', '/api/v2/articles', ['articles' => [[
            'id' => $article->id,
            'remarque' => 'après',
            'statut' => true,
            'emplacement' => ['couleur_id' => $couleur->id, 'parent_id' => $emplacement->id],
        ]]], ['Sis-Key' => 1])->assertStatus(422);

        $this->assertSame('avant', $article->fresh()->remarque);
    }
}
