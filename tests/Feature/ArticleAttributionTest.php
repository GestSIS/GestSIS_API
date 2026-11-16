<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\AttributionBusiness;
use App\Models\Article;
use App\Models\ArticleAttribution;
use App\Models\Emplacement;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ArticleAttributionTest extends TestCase
{
    use DatabaseTransactions;

    private function articleAttribuable(array $extra = []): Article
    {
        return Article::factory()->create(array_merge([
            'materiel_type_id' => MaterielType::factory()->create(['est_attribuable' => true])->id,
            'emplacement_id' => Emplacement::factory()->create()->id,
            'sapeur_id' => null,
        ], $extra));
    }

    /**
     * Article attribué à $sapeur depuis $debut (attribution ouverte en base).
     */
    private function articleAttribue(Sapeur $sapeur, string $debut): Article
    {
        $article = $this->articleAttribuable(['sapeur_id' => $sapeur->id, 'emplacement_id' => null]);
        ArticleAttribution::factory()->create([
            'article_id' => $article->id,
            'sapeur_id' => $sapeur->id,
            'debut' => $debut,
        ]);

        return $article;
    }

    private function attribuer(Sapeur $sapeur, string $date, array $articleIds)
    {
        return $this->json('POST', "/api/v2/sapeurs/{$sapeur->id}/articles", [
            'date' => $date,
            'articleIds' => $articleIds,
        ], ['Sis-Key' => 1]);
    }

    private function rendre(Emplacement $emplacement, string $date, array $articleIds)
    {
        return $this->json('POST', "/api/v2/emplacements/{$emplacement->id}/articles", [
            'date' => $date,
            'articleIds' => $articleIds,
        ], ['Sis-Key' => 1]);
    }

    public function testAttribuerOuvreUneAttribution(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleAttribuable();

        $this->attribuer($sapeur, '2026-03-01', [$article->id])->assertStatus(201);

        $this->assertDatabaseHas('article_attributions', [
            'article_id' => $article->id,
            'sapeur_id' => $sapeur->id,
            'debut' => '2026-03-01',
            'fin' => null,
        ]);
        $article->refresh();
        $this->assertSame($sapeur->id, $article->sapeur_id);
        $this->assertNull($article->emplacement_id);
    }

    public function testRetourClotLAttributionEtRangeLArticle(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleAttribue($sapeur, '2026-02-01');
        $emplacement = Emplacement::factory()->create();

        $this->rendre($emplacement, '2026-04-01', [$article->id])->assertStatus(201);

        $this->assertDatabaseHas('article_attributions', [
            'article_id' => $article->id,
            'sapeur_id' => $sapeur->id,
            'debut' => '2026-02-01',
            'fin' => '2026-04-01',
            'emplacement_retour_id' => $emplacement->id,
        ]);
        $article->refresh();
        $this->assertNull($article->sapeur_id);
        $this->assertSame($emplacement->id, $article->emplacement_id);
    }

    public function testTransfertClotEtOuvreLeMemeJour(): void
    {
        $ancien = Sapeur::factory()->create();
        $nouveau = Sapeur::factory()->create();
        $article = $this->articleAttribue($ancien, '2026-02-01');

        $this->attribuer($nouveau, '2026-05-10', [$article->id])->assertStatus(201);

        $this->assertDatabaseHas('article_attributions', [
            'article_id' => $article->id,
            'sapeur_id' => $ancien->id,
            'fin' => '2026-05-10',
            'emplacement_retour_id' => null,
        ]);
        $this->assertDatabaseHas('article_attributions', [
            'article_id' => $article->id,
            'sapeur_id' => $nouveau->id,
            'debut' => '2026-05-10',
            'fin' => null,
        ]);
        $this->assertSame($nouveau->id, $article->fresh()->sapeur_id);
    }

    public function testTransfertAvantLeDebutDeLAttributionEnCoursEstRefuse(): void
    {
        $ancien = Sapeur::factory()->create();
        $nouveau = Sapeur::factory()->create();
        $article = $this->articleAttribue($ancien, '2026-05-10');

        $this->attribuer($nouveau, '2026-05-01', [$article->id])->assertStatus(422)->assertJsonStructure(['message']);

        $this->assertSame($ancien->id, $article->fresh()->sapeur_id);
        $this->assertSame(1, ArticleAttribution::where('article_id', $article->id)->count());
        $this->assertDatabaseHas('article_attributions', ['article_id' => $article->id, 'sapeur_id' => $ancien->id, 'fin' => null]);
    }

    public function testRetourAvantLeDebutEstRefuse(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleAttribue($sapeur, '2026-05-10');

        $this->rendre(Emplacement::factory()->create(), '2026-05-01', [$article->id])->assertStatus(422);

        $this->assertSame($sapeur->id, $article->fresh()->sapeur_id);
        $this->assertDatabaseHas('article_attributions', ['article_id' => $article->id, 'fin' => null]);
    }

    public function testAttributionAvantLaFinDeLaPrecedenteEstRefusee(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleAttribuable();
        ArticleAttribution::factory()->create([
            'article_id' => $article->id,
            'sapeur_id' => Sapeur::factory()->create()->id,
            'debut' => '2026-01-01',
            'fin' => '2026-06-01',
        ]);

        $this->attribuer($sapeur, '2026-05-01', [$article->id])->assertStatus(422);

        $this->assertNull($article->fresh()->sapeur_id);
        $this->assertDatabaseMissing('article_attributions', ['article_id' => $article->id, 'fin' => null]);
    }

    public function testAttributionDateFutureEstRefusee(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleAttribuable();

        $this->attribuer($sapeur, now()->addDay()->toDateString(), [$article->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);

        $this->rendre(Emplacement::factory()->create(), now()->addDay()->toDateString(), [$article->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);

        $this->assertDatabaseMissing('article_attributions', ['article_id' => $article->id]);
    }

    public function testAttribuerUnArticleSupprimeEstRefuse(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleAttribuable(['supprime_le' => '2026-01-15']);

        $this->attribuer($sapeur, '2026-03-01', [$article->id])->assertStatus(422)->assertJsonStructure(['message']);

        $this->assertNull($article->fresh()->sapeur_id);
        $this->assertDatabaseMissing('article_attributions', ['article_id' => $article->id]);
    }

    public function testCreerArticleAttribueOuvreUneAttribution(): void
    {
        $type = MaterielType::factory()->create(['est_attribuable' => true]);
        $sapeur = Sapeur::factory()->create();

        $response = $this->json('POST', '/api/v2/articles', ['articles' => [[
            'materiel_type_id' => $type->id,
            'quantite' => 1,
            'sapeur_id' => $sapeur->id,
            'attribution' => '2026-10-29',
        ]]], ['Sis-Key' => 1])->assertStatus(200);

        $articleId = $response->json('data.0.id');
        $this->assertDatabaseHas('articles', ['id' => $articleId, 'sapeur_id' => $sapeur->id, 'emplacement_id' => null]);
        $this->assertDatabaseHas('article_attributions', [
            'article_id' => $articleId,
            'sapeur_id' => $sapeur->id,
            'debut' => '2026-10-29',
            'fin' => null,
        ]);
    }

    public function testCreerArticleAttribueDateFutureEstRefusee(): void
    {
        $type = MaterielType::factory()->create(['est_attribuable' => true]);

        $this->json('POST', '/api/v2/articles', ['articles' => [[
            'materiel_type_id' => $type->id,
            'quantite' => 1,
            'sapeur_id' => Sapeur::factory()->create()->id,
            'attribution' => now()->addDay()->toDateString(),
        ]]], ['Sis-Key' => 1])->assertStatus(422)->assertJsonValidationErrors(['articles.0.attribution']);

        $this->assertDatabaseMissing('articles', ['materiel_type_id' => $type->id]);
    }

    public function testEditArticleIgnoreLeChangementDeSapeur(): void
    {
        $sapeur = Sapeur::factory()->create();
        $autre = Sapeur::factory()->create();
        $article = $this->articleAttribue($sapeur, '2026-02-01');

        $this->json('PUT', '/api/v2/articles', ['articles' => [[
            'id' => $article->id,
            'statut' => true,
            'sapeur_id' => $autre->id,
            'attribution' => '2026-06-01',
            'emplacement_id' => null,
            'remarque' => 'modifiée',
        ]]], ['Sis-Key' => 1])->assertStatus(200);

        $article->refresh();
        $this->assertSame($sapeur->id, $article->sapeur_id);
        $this->assertSame('modifiée', $article->remarque);
        $this->assertSame(1, ArticleAttribution::where('article_id', $article->id)->count());
        $this->assertDatabaseMissing('article_attributions', ['sapeur_id' => $autre->id]);
    }

    public function testHistoriqueAttributionsDUnArticle(): void
    {
        $premier = Sapeur::factory()->create(['nom' => 'Alpha', 'prenom' => 'Anne']);
        $second = Sapeur::factory()->create();
        $article = $this->articleAttribue($second, '2026-04-01');
        ArticleAttribution::factory()->create([
            'article_id' => $article->id,
            'sapeur_id' => $premier->id,
            'debut' => '2026-01-01',
            'fin' => '2026-03-01',
        ]);

        $response = $this->json('GET', "/api/v2/articles/{$article->id}/attributions", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['*' => [
            'id', 'article_id', 'sapeur_id', 'debut', 'fin', 'emplacement_retour_id',
            'article' => ['id', 'materiel_type'],
            'sapeur' => ['id', 'nom_prenom'],
        ]]]);
        $this->assertSame([$second->id, $premier->id], collect($response->json('data'))->pluck('sapeur_id')->all());
        $this->assertSame('Alpha Anne', $response->json('data.1.sapeur.nom_prenom'));
    }

    public function testHistoriqueAttributionsDUnSapeur(): void
    {
        $sapeur = Sapeur::factory()->create();
        $ancien = $this->articleAttribuable();
        ArticleAttribution::factory()->create([
            'article_id' => $ancien->id,
            'sapeur_id' => $sapeur->id,
            'debut' => '2025-06-01',
            'fin' => '2025-12-01',
        ]);
        $actuel = $this->articleAttribue($sapeur, '2026-02-01');
        // Attribution d'un autre sapeur : ne doit pas apparaître
        ArticleAttribution::factory()->create(['debut' => '2026-03-01']);

        $response = $this->json('GET', "/api/v2/sapeurs/{$sapeur->id}/attributions", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['*' => [
            'id', 'article_id', 'sapeur_id', 'debut', 'fin', 'emplacement_retour_id',
            'article' => ['id', 'materiel_type'],
            'sapeur' => ['id', 'nom_prenom'],
        ]]]);
        $this->assertSame([$actuel->id, $ancien->id], collect($response->json('data'))->pluck('article_id')->all());
    }

    public function testOuvrirEnregistreLAuteur(): void
    {
        $sapeur = Sapeur::factory()->create();
        $auteur = Sapeur::factory()->create();
        $article = $this->articleAttribuable();

        $attribution = AttributionBusiness::ouvrir($article, $sapeur->id, '2026-03-01', $auteur->id);

        $this->assertSame($auteur->id, $attribution->attribue_par_sapeur_id);
        $this->assertDatabaseHas('article_attributions', [
            'id' => $attribution->id,
            'attribue_par_sapeur_id' => $auteur->id,
        ]);
    }

    public function testCloturerEnregistreLAuteur(): void
    {
        $sapeur = Sapeur::factory()->create();
        $auteur = Sapeur::factory()->create();
        $article = $this->articleAttribue($sapeur, '2026-02-01');
        $emplacement = Emplacement::factory()->create();

        AttributionBusiness::cloturer($article, '2026-03-01', $emplacement->id, $auteur->id);

        $this->assertDatabaseHas('article_attributions', [
            'article_id' => $article->id,
            'fin' => '2026-03-01',
            'emplacement_retour_id' => $emplacement->id,
            'cloture_par_sapeur_id' => $auteur->id,
        ]);
        $this->assertSame($emplacement->id, $article->fresh()->emplacement_id);
    }
}
