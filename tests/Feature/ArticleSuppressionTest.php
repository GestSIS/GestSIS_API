<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\ArticleBusiness;
use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
use App\Models\ArticleAttribution;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\Emplacement;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ArticleSuppressionTest extends TestCase
{
    use DatabaseTransactions;

    private const DATE_SUPPRESSION = '2026-11-01';

    /**
     * @param int[] $articleIds
     */
    private function supprimer(array $articleIds, string $date = self::DATE_SUPPRESSION, ?string $remarque = null): TestResponse
    {
        return $this->json('DELETE', '/api/v2/articles', [
            'articleIds' => $articleIds,
            'date' => $date,
            'remarque' => $remarque,
        ], ['Sis-Key' => 1]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function supprimerLot(array $data): TestResponse
    {
        return $this->json('POST', '/api/v2/articles/supprimer-lot', array_merge([
            'date' => self::DATE_SUPPRESSION,
        ], $data), ['Sis-Key' => 1]);
    }

    /**
     * @param int[] $articleIds
     */
    private function restaurer(array $articleIds, ?int $emplacementId): TestResponse
    {
        return $this->json('POST', '/api/v2/articles/restaurer', array_filter([
            'articleIds' => $articleIds,
            'emplacement_id' => $emplacementId,
        ], fn ($value) => $value !== null), ['Sis-Key' => 1]);
    }

    /**
     * Véhicule avec son emplacement représenté.
     *
     * @return array{0: Article, 1: Emplacement}
     */
    private function vehicule(array $articleExtra = [], array $emplacementExtra = []): array
    {
        $type = MaterielType::factory()->create([
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_emplacement' => true,
        ]);
        $article = Article::factory()->create(array_merge(['materiel_type_id' => $type->id], $articleExtra));
        $emplacement = Emplacement::factory()->create(array_merge(['article_id' => $article->id], $emplacementExtra));

        return [$article, $emplacement];
    }

    public function testSupprimerArticleRemplitDateEtRemarqueSansEffacer(): void
    {
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create(['emplacement_id' => $emplacement->id]);

        $this->supprimer([$article->id], remarque: 'Clé perdue')->assertStatus(204);

        $article->refresh();
        $this->assertSame(self::DATE_SUPPRESSION, $article->supprime_le->toDateString());
        $this->assertSame('Clé perdue', $article->remarque_suppression);
        $this->assertSame($emplacement->id, $article->emplacement_id);
    }

    public function testSupprimerArticleEnregistreLAuteur(): void
    {
        $auteur = Sapeur::factory()->create();
        $article = Article::factory()->create();

        ArticleBusiness::supprimerArticles([$article->id], self::DATE_SUPPRESSION, null, $auteur->id);

        $this->assertSame($auteur->id, $article->fresh()->supprime_par_sapeur_id);
    }

    public function testSupprimerArticleAvecDateFutureEstRefuse(): void
    {
        $article = Article::factory()->create();

        $this->supprimer([$article->id], now()->addDay()->toDateString())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);

        $this->assertNull($article->fresh()->supprime_le);
    }

    public function testSupprimerArticleSansDateEstRefuse(): void
    {
        $article = Article::factory()->create();

        $this->json('DELETE', '/api/v2/articles', ['articleIds' => [$article->id]], ['Sis-Key' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function testSupprimerArticleAttribueClotLAttributionEtVideSapeur(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = Article::factory()->create(['sapeur_id' => $sapeur->id]);
        ArticleAttribution::factory()->create([
            'article_id' => $article->id,
            'sapeur_id' => $sapeur->id,
            'debut' => '2026-01-01',
        ]);

        $this->supprimer([$article->id])->assertStatus(204);

        $this->assertDatabaseHas('article_attributions', [
            'article_id' => $article->id,
            'sapeur_id' => $sapeur->id,
            'fin' => self::DATE_SUPPRESSION,
        ]);
        $article->refresh();
        $this->assertNull($article->sapeur_id);
        $this->assertNull($article->emplacement_id);
        $this->assertNotNull($article->supprime_le);
    }

    public function testSupprimerArticleAyantDesControlesEstAccepte(): void
    {
        $article = Article::factory()->create();
        $controle = Controle::create(['nom' => 'Contrôle test', 'recurrence_type' => 'NON_PERIODIQUE']);
        ControleExec::create([
            'controle_id' => $controle->id,
            'article_id' => $article->id,
            'executed_at' => '2026-01-01',
            'executed_by' => Sapeur::factory()->create()->id,
            'trigger_type' => 'NON_PERIODIQUE',
        ]);

        $this->supprimer([$article->id])->assertStatus(204);

        $this->assertNotNull($article->fresh()->supprime_le);
        $this->assertDatabaseHas('controle_execs', ['article_id' => $article->id]);
    }

    public function testSupprimerUnArticleDejaSupprimeEstRefuse(): void
    {
        $article = Article::factory()->supprime('2026-02-01')->create(['remarque_suppression' => 'Cassé']);

        $this->supprimer([$article->id], remarque: 'Autre')->assertStatus(422)->assertJsonStructure(['message']);

        $article->refresh();
        $this->assertSame('2026-02-01', $article->supprime_le->toDateString());
        $this->assertSame('Cassé', $article->remarque_suppression);
    }

    public function testSupprimerVehiculeDesactiveSonEmplacement(): void
    {
        [$vehicule, $emplacement] = $this->vehicule();

        $this->supprimer([$vehicule->id])->assertStatus(204);

        $this->assertNotNull($vehicule->fresh()->supprime_le);
        $this->assertFalse($emplacement->fresh()->statut);
    }

    public function testSupprimerVehiculeContenantDuMaterielNonSupprimeEstRefuse(): void
    {
        [$vehicule, $emplacement] = $this->vehicule();
        Article::factory()->create(['emplacement_id' => $emplacement->id]);

        $this->supprimer([$vehicule->id])->assertStatus(422)->assertJsonStructure(['message']);

        $this->assertNull($vehicule->fresh()->supprime_le);
        $this->assertTrue($emplacement->fresh()->statut);
    }

    public function testSupprimerVehiculeContenantUnSousEmplacementActifEstRefuse(): void
    {
        [$vehicule, $emplacement] = $this->vehicule();
        Emplacement::factory()->create(['parent_id' => $emplacement->id]);

        $this->supprimer([$vehicule->id])->assertStatus(422)->assertJsonStructure(['message']);

        $this->assertNull($vehicule->fresh()->supprime_le);
        $this->assertTrue($emplacement->fresh()->statut);
    }

    public function testSupprimerVehiculeNeContenantQueDesArticlesSupprimesEstAccepte(): void
    {
        [$vehicule, $emplacement] = $this->vehicule();
        Article::factory()->supprime()->create(['emplacement_id' => $emplacement->id]);

        $this->supprimer([$vehicule->id])->assertStatus(204);

        $this->assertNotNull($vehicule->fresh()->supprime_le);
        $this->assertFalse($emplacement->fresh()->statut);
    }

    public function testSupprimerLotPrendLesPeremptionsLesPlusProchesDAbord(): void
    {
        $type = MaterielType::factory()->create();
        $emplacement = Emplacement::factory()->create();
        $lot = ['materiel_type_id' => $type->id, 'emplacement_id' => $emplacement->id, 'compartiment' => 'A'];
        $janvier = Article::factory()->create([...$lot, 'date_peremption' => '2027-01-01']);
        $decembre = Article::factory()->create([...$lot, 'date_peremption' => '2026-12-01']);
        $sansPeremption = Article::factory()->create([...$lot, 'date_peremption' => null]);

        $response = $this->supprimerLot([...$lot, 'quantite' => 2, 'remarque' => 'Périmé'])->assertStatus(200);

        $this->assertEqualsCanonicalizing([$decembre->id, $janvier->id], array_column($response->json('data'), 'id'));
        $this->assertNotNull($decembre->fresh()->supprime_le);
        $this->assertNotNull($janvier->fresh()->supprime_le);
        $this->assertSame('Périmé', $janvier->fresh()->remarque_suppression);
        $this->assertNull($sansPeremption->fresh()->supprime_le);
    }

    public function testSupprimerLotAQuantiteEgaleCreationLaPlusAncienneDAbord(): void
    {
        $type = MaterielType::factory()->create();
        $emplacement = Emplacement::factory()->create();
        $lot = ['materiel_type_id' => $type->id, 'emplacement_id' => $emplacement->id];
        $recent = Article::factory()->create([...$lot, 'created_at' => '2026-05-01 10:00:00']);
        $ancien = Article::factory()->create([...$lot, 'created_at' => '2026-01-01 10:00:00']);
        $memeDateA = Article::factory()->create([...$lot, 'created_at' => '2026-03-01 10:00:00']);
        $memeDateB = Article::factory()->create([...$lot, 'created_at' => '2026-03-01 10:00:00']);

        $this->supprimerLot([...$lot, 'quantite' => 2])->assertStatus(200);

        $this->assertNotNull($ancien->fresh()->supprime_le);
        $this->assertNotNull($memeDateA->fresh()->supprime_le);
        $this->assertNull($memeDateB->fresh()->supprime_le);
        $this->assertNull($recent->fresh()->supprime_le);
    }

    public function testSupprimerLotIgnoreLesAutresCompartimentsEtLesPiecesDejaSupprimees(): void
    {
        $type = MaterielType::factory()->create();
        $emplacement = Emplacement::factory()->create();
        $lot = ['materiel_type_id' => $type->id, 'emplacement_id' => $emplacement->id, 'compartiment' => 'A'];
        Article::factory()->supprime()->create([...$lot, 'created_at' => '2026-01-01 10:00:00']);
        Article::factory()->create([...$lot, 'compartiment' => 'B', 'created_at' => '2026-01-02 10:00:00']);
        $piece = Article::factory()->create([...$lot, 'created_at' => '2026-01-03 10:00:00']);

        $response = $this->supprimerLot([...$lot, 'quantite' => 1])->assertStatus(200);

        $this->assertSame([$piece->id], array_column($response->json('data'), 'id'));
    }

    public function testSupprimerLotQuantiteTropGrandeEstRefuseSansRienSupprimer(): void
    {
        $type = MaterielType::factory()->create();
        $emplacement = Emplacement::factory()->create();
        $lot = ['materiel_type_id' => $type->id, 'emplacement_id' => $emplacement->id];
        Article::factory()->count(2)->create($lot);

        $this->supprimerLot([...$lot, 'quantite' => 3])->assertStatus(422)->assertJsonStructure(['message']);

        $this->assertSame(0, Article::where('materiel_type_id', $type->id)->whereNotNull('supprime_le')->count());
    }

    public function testRestaurerArticleVideLesChampsDeSuppressionEtLeRange(): void
    {
        $ancien = Emplacement::factory()->create(['statut' => false]);
        $nouveau = Emplacement::factory()->create();
        $article = Article::factory()->supprime()->create([
            'emplacement_id' => $ancien->id,
            'supprime_par_sapeur_id' => Sapeur::factory()->create()->id,
            'remarque_suppression' => 'Perdu',
        ]);

        $response = $this->restaurer([$article->id], $nouveau->id)->assertStatus(200);

        $this->assertSame([$article->id], array_column($response->json('data'), 'id'));
        $article->refresh();
        $this->assertNull($article->supprime_le);
        $this->assertNull($article->supprime_par_sapeur_id);
        $this->assertNull($article->remarque_suppression);
        $this->assertSame($nouveau->id, $article->emplacement_id);
    }

    public function testRestaurerNeRouvrePasLAttribution(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = Article::factory()->supprime('2026-06-01')->create();
        ArticleAttribution::factory()->create([
            'article_id' => $article->id,
            'sapeur_id' => $sapeur->id,
            'debut' => '2026-01-01',
            'fin' => '2026-06-01',
        ]);

        $this->restaurer([$article->id], Emplacement::factory()->create()->id)->assertStatus(200);

        $this->assertNull($article->fresh()->sapeur_id);
        $this->assertDatabaseMissing('article_attributions', ['article_id' => $article->id, 'fin' => null]);
    }

    public function testRestaurerDansUnEmplacementInactifEstRefuse(): void
    {
        $inactif = Emplacement::factory()->create(['statut' => false]);
        $article = Article::factory()->supprime()->create();

        $this->restaurer([$article->id], $inactif->id)->assertStatus(422)->assertJsonStructure(['message']);

        $article->refresh();
        $this->assertNotNull($article->supprime_le);
        $this->assertNull($article->emplacement_id);
    }

    public function testRestaurerUnArticleNonSupprimeEstRefuse(): void
    {
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create(['emplacement_id' => $emplacement->id]);

        $this->restaurer([$article->id], Emplacement::factory()->create()->id)->assertStatus(422);

        $this->assertSame($emplacement->id, $article->fresh()->emplacement_id);
    }

    public function testRestaurerVehiculeReactiveSonEmplacement(): void
    {
        [$vehicule, $emplacement] = $this->vehicule(['supprime_le' => '2026-01-01'], ['statut' => false]);

        $this->restaurer([$vehicule->id], Emplacement::factory()->create()->id)->assertStatus(200);

        $vehicule->refresh();
        $this->assertNull($vehicule->supprime_le);
        $this->assertNull($vehicule->emplacement_id);
        $this->assertTrue($emplacement->fresh()->statut);
    }

    public function testRestaurerVehiculeSousUnParentInactifEstRefuse(): void
    {
        $parent = Emplacement::factory()->create(['statut' => false]);
        [$vehicule, $emplacement] = $this->vehicule(
            ['supprime_le' => '2026-01-01'],
            ['statut' => false, 'parent_id' => $parent->id],
        );

        $this->restaurer([$vehicule->id], Emplacement::factory()->create()->id)->assertStatus(422);

        $this->assertNotNull($vehicule->fresh()->supprime_le);
        $this->assertFalse($emplacement->fresh()->statut);
    }

    public function testRestaurerVehiculeSansEmplacementEstAccepte(): void
    {
        [$vehicule, $emplacement] = $this->vehicule(['supprime_le' => '2026-01-01'], ['statut' => false]);

        $this->restaurer([$vehicule->id], null)->assertStatus(200);

        $this->assertNull($vehicule->fresh()->supprime_le);
        $this->assertTrue($emplacement->fresh()->statut);
    }

    public function testRestaurerArticleSansEmplacementEstRefuse(): void
    {
        $article = Article::factory()->supprime()->create();

        $this->restaurer([$article->id], null)->assertStatus(422)->assertJsonStructure(['message']);

        $this->assertNotNull($article->fresh()->supprime_le);
    }

    public function testSupprimerLotDUnTypeNumeroteEstRefuse(): void
    {
        $type = MaterielType::factory()->numerote()->create();
        $emplacement = Emplacement::factory()->create();
        $lot = ['materiel_type_id' => $type->id, 'emplacement_id' => $emplacement->id];
        Article::factory()->count(2)->create($lot);

        $this->supprimerLot([...$lot, 'quantite' => 1])->assertStatus(422)->assertJsonStructure(['message']);

        $this->assertSame(0, Article::where('materiel_type_id', $type->id)->whereNotNull('supprime_le')->count());
    }

    public function testModifierUnArticleSupprimeEstRefuse(): void
    {
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->supprime()->create(['emplacement_id' => $emplacement->id, 'remarque' => 'avant']);

        $this->json('PUT', '/api/v2/articles', ['articles' => [[
            'id' => $article->id,
            'emplacement_id' => Emplacement::factory()->create()->id,
            'remarque' => 'après',
        ]]], ['Sis-Key' => 1])->assertStatus(422)->assertJsonStructure(['message']);

        $article->refresh();
        $this->assertSame($emplacement->id, $article->emplacement_id);
        $this->assertSame('avant', $article->remarque);
    }
}
