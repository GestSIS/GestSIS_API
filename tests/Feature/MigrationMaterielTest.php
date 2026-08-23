<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
use App\Models\Couleur;
use App\Models\Emplacement;
use App\Models\Hangar;
use App\Models\Localite;
use App\Models\MaterielType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MigrationMaterielTest extends TestCase
{
    use DatabaseTransactions;

    private function vehiculeType(): MaterielType
    {
        return MaterielType::factory()->create([
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_emplacement' => true,
        ]);
    }

    public function testEmplacementsSansHangarExcludesHangarsEtArticleEmplacements(): void
    {
        $couleur = Couleur::factory()->create();
        $sansHangar = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $avecHangar = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        Hangar::create(['id' => $avecHangar->id, 'localite_id' => Localite::inRandomOrder()->first()->id]);
        $article = Article::factory()->create(['emplacement_id' => null, 'sapeur_id' => null]);
        Emplacement::factory()->create(['couleur_id' => $couleur->id, 'article_id' => $article->id]);

        $response = $this->json('GET', '/api/v2/admin/migration/emplacements-sans-hangar', [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($sansHangar->id));
        $this->assertFalse($ids->contains($avecHangar->id));
    }

    public function testTransformerEnHangarCreeLeHangar(): void
    {
        $couleur = Couleur::factory()->create();
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $localite = Localite::inRandomOrder()->first();

        $response = $this->json('POST', "/api/v2/admin/migration/emplacements/{$emplacement->id}/hangar", [
            'rue' => 'Rue Test',
            'no_rue' => '7',
            'localite_id' => $localite->id,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hangars', [
            'id' => $emplacement->id,
            'rue' => 'Rue Test',
            'localite_id' => $localite->id,
        ]);
    }

    public function testTransformerEnHangarWithBlankRueDoesNotCrash(): void
    {
        // Régression : le middleware ConvertEmptyStringsToNull transforme une
        // chaîne vide envoyée par le formulaire en null avant validation ; rue/no_rue
        // sont NOT NULL en base (avec défaut '').
        $couleur = Couleur::factory()->create();
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $localite = Localite::inRandomOrder()->first();

        $response = $this->json('POST', "/api/v2/admin/migration/emplacements/{$emplacement->id}/hangar", [
            'rue' => '',
            'no_rue' => '',
            'localite_id' => $localite->id,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hangars', [
            'id' => $emplacement->id,
            'rue' => '',
            'no_rue' => '',
            'localite_id' => $localite->id,
        ]);
    }

    public function testTransformerEnHangarRejectedWhenAlreadyHangar(): void
    {
        $couleur = Couleur::factory()->create();
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $localite = Localite::inRandomOrder()->first();
        Hangar::create(['id' => $emplacement->id, 'localite_id' => $localite->id]);

        $response = $this->json('POST', "/api/v2/admin/migration/emplacements/{$emplacement->id}/hangar", [
            'localite_id' => $localite->id,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
    }

    public function testVehiculesSansEmplacementListsOnlyUnlinkedVehicules(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $sansLien = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        $avecLien = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        Emplacement::factory()->create(['couleur_id' => $couleur->id, 'article_id' => $avecLien->id]);

        $response = $this->json('GET', '/api/v2/admin/migration/vehicules-sans-emplacement', [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($sansLien->id));
        $this->assertFalse($ids->contains($avecLien->id));
    }

    public function testLierEmplacementFusionneAvecEmplacementExistant(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => null,
            'sapeur_id' => null,
            'designation' => 'Tonne-Pompe',
            'remarque' => 'Note véhicule',
        ]);
        $emplacementExistant = Emplacement::factory()->create([
            'couleur_id' => $couleur->id,
            'designation' => 'Ancien nom',
        ]);

        $response = $this->json('POST', "/api/v2/admin/migration/articles/{$article->id}/emplacement", [
            'emplacement_id' => $emplacementExistant->id,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('emplacements', [
            'id' => $emplacementExistant->id,
            'article_id' => $article->id,
            'designation' => 'Tonne-Pompe',
            'remarque' => 'Note véhicule',
        ]);
    }

    public function testLierEmplacementRejectedWhenArticleAlreadyLinked(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        Emplacement::factory()->create(['couleur_id' => $couleur->id, 'article_id' => $article->id]);
        $autreEmplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);

        $response = $this->json('POST', "/api/v2/admin/migration/articles/{$article->id}/emplacement", [
            'emplacement_id' => $autreEmplacement->id,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
    }

    public function testLierEmplacementRejectedWhenTargetEmplacementAlreadyLinked(): void
    {
        $type = $this->vehiculeType();
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => null, 'sapeur_id' => null]);
        $autreArticle = Article::factory()->create(['emplacement_id' => null, 'sapeur_id' => null]);
        $emplacementDejaLie = Emplacement::factory()->create(['couleur_id' => $couleur->id, 'article_id' => $autreArticle->id]);

        $response = $this->json('POST', "/api/v2/admin/migration/articles/{$article->id}/emplacement", [
            'emplacement_id' => $emplacementDejaLie->id,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
    }

    public function testLierEmplacementRejectedWhenArticleIsNotEstEmplacementType(): void
    {
        $couleur = Couleur::factory()->create();
        $article = Article::factory()->create(['emplacement_id' => null, 'sapeur_id' => null]);
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);

        $response = $this->json('POST', "/api/v2/admin/migration/articles/{$article->id}/emplacement", [
            'emplacement_id' => $emplacement->id,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id, 'article_id' => null]);
    }
}
