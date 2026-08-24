<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Couleur;
use App\Models\Emplacement;
use App\Models\Hangar;
use App\Models\Localite;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmplacementTest extends TestCase
{
    use DatabaseTransactions;

    public function testStoreEmplacementComputesTriFromEmplacementsTable(): void
    {
        $couleur = Couleur::factory()->create();
        $maxBefore = (int) (Emplacement::max('id') ?? 0);

        $response = $this->json('POST', '/api/v2/emplacements', [
            'designation' => 'Armoire A',
            'est_etiquete' => false,
            'est_compartimentable' => false,
            'couleur_id' => $couleur->id,
            'parent_id' => null,
            'statut' => true,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        // tri doit être calculé sur la table emplacements (et non materiel_categories)
        $this->assertSame($maxBefore + 1, $response->json('data.tri'));
        $this->assertDatabaseHas('emplacements', [
            'id' => $response->json('data.id'),
            'designation' => 'Armoire A',
            'tri' => $maxBefore + 1,
        ]);
    }

    public function testCannotUpdateEmplacementLinkedToAnArticleDirectly(): void
    {
        $article = Article::factory()->create(['emplacement_id' => null, 'sapeur_id' => null]);
        $emplacement = Emplacement::factory()->create(['article_id' => $article->id]);

        $response = $this->json('PUT', "/api/v2/emplacements/{$emplacement->id}", [
            'designation' => 'Nouvelle désignation',
            'remarque' => '',
            'est_etiquete' => false,
            'est_compartimentable' => false,
            'couleur_id' => $emplacement->couleur_id,
            'parent_id' => null,
            'statut' => true,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id, 'designation' => $emplacement->designation]);
    }

    public function testCannotDeleteEmplacementLinkedToAnArticleDirectly(): void
    {
        $article = Article::factory()->create(['emplacement_id' => null, 'sapeur_id' => null]);
        $emplacement = Emplacement::factory()->create(['article_id' => $article->id]);

        $response = $this->json('DELETE', "/api/v2/emplacements/{$emplacement->id}", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id]);
    }

    public function testCannotDeleteEmplacementWithSousEmplacements(): void
    {
        $parent = Emplacement::factory()->create();
        Emplacement::factory()->create(['parent_id' => $parent->id]);

        $response = $this->json('DELETE', "/api/v2/emplacements/{$parent->id}", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('emplacements', ['id' => $parent->id]);
    }

    private function basePayload(array $extra = []): array
    {
        $couleur = Couleur::factory()->create();

        return array_merge([
            'designation' => 'Hangar Bassecourt',
            'remarque' => '',
            'est_etiquete' => false,
            'est_compartimentable' => false,
            'couleur_id' => $couleur->id,
            'parent_id' => null,
            'statut' => true,
        ], $extra);
    }

    public function testStoreEmplacementWithHangarCreatesLinkedHangarRow(): void
    {
        $localite = Localite::inRandomOrder()->first();

        $payload = $this->basePayload([
            'hangar' => ['rue' => 'Rue Principale', 'no_rue' => '12', 'localite_id' => $localite->id],
        ]);

        $response = $this->json('POST', '/api/v2/emplacements', $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $id = $response->json('data.id');
        $this->assertDatabaseHas('hangars', [
            'id' => $id,
            'rue' => 'Rue Principale',
            'no_rue' => '12',
            'localite_id' => $localite->id,
        ]);
    }

    public function testStoreEmplacementWithoutHangarDoesNotCreateHangarRow(): void
    {
        $payload = $this->basePayload();

        $response = $this->json('POST', '/api/v2/emplacements', $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('hangars', ['id' => $response->json('data.id')]);
    }

    public function testUpdateEmplacementWithHangarUpsertsHangarRow(): void
    {
        $couleur = Couleur::factory()->create();
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $localite = Localite::inRandomOrder()->first();

        $payload = $this->basePayload([
            'couleur_id' => $couleur->id,
            'hangar' => ['rue' => 'Rue du Stand', 'no_rue' => '3', 'localite_id' => $localite->id],
        ]);

        $response = $this->json('PUT', "/api/v2/emplacements/{$emplacement->id}", $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hangars', [
            'id' => $emplacement->id,
            'rue' => 'Rue du Stand',
            'localite_id' => $localite->id,
        ]);
    }

    public function testStoreEmplacementWithBlankHangarRueDoesNotCrash(): void
    {
        // Régression : le middleware ConvertEmptyStringsToNull transforme une
        // chaîne vide envoyée par le formulaire en null avant validation ; rue/no_rue
        // sont NOT NULL en base (avec défaut '').
        $localite = Localite::inRandomOrder()->first();

        $payload = $this->basePayload([
            'hangar' => ['rue' => '', 'no_rue' => '', 'localite_id' => $localite->id],
        ]);

        $response = $this->json('POST', '/api/v2/emplacements', $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hangars', [
            'id' => $response->json('data.id'),
            'rue' => '',
            'no_rue' => '',
            'localite_id' => $localite->id,
        ]);
    }

    public function testUpdateEmplacementWithBlankHangarRueDoesNotCrash(): void
    {
        $couleur = Couleur::factory()->create();
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $localite = Localite::inRandomOrder()->first();

        $payload = $this->basePayload([
            'couleur_id' => $couleur->id,
            'hangar' => ['rue' => '', 'no_rue' => '', 'localite_id' => $localite->id],
        ]);

        $response = $this->json('PUT', "/api/v2/emplacements/{$emplacement->id}", $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hangars', [
            'id' => $emplacement->id,
            'rue' => '',
            'no_rue' => '',
        ]);
    }

    public function testUpdateEmplacementWithoutHangarKeyDoesNotEraseExistingHangar(): void
    {
        $couleur = Couleur::factory()->create();
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $localite = Localite::inRandomOrder()->first();
        Hangar::create(['id' => $emplacement->id, 'rue' => 'Rue existante', 'localite_id' => $localite->id]);

        // Edition « générique » (ModalEmplacement), sans connaissance du hangar.
        $payload = $this->basePayload(['couleur_id' => $couleur->id, 'designation' => 'Nouveau nom']);

        $response = $this->json('PUT', "/api/v2/emplacements/{$emplacement->id}", $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hangars', ['id' => $emplacement->id, 'rue' => 'Rue existante']);
    }

    public function testCannotSetEmplacementAsItsOwnParent(): void
    {
        $couleur = Couleur::factory()->create();
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);

        $payload = $this->basePayload(['couleur_id' => $couleur->id, 'parent_id' => $emplacement->id]);

        $response = $this->json('PUT', "/api/v2/emplacements/{$emplacement->id}", $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('emplacements', ['id' => $emplacement->id, 'parent_id' => null]);
    }

    public function testCannotCreateIndirectCycleInEmplacementHierarchy(): void
    {
        $couleur = Couleur::factory()->create();
        // A est parent de B ; tenter de mettre A sous B créerait un cycle A -> B -> A.
        $a = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $b = Emplacement::factory()->create(['couleur_id' => $couleur->id, 'parent_id' => $a->id]);

        $payload = $this->basePayload(['couleur_id' => $couleur->id, 'parent_id' => $b->id]);

        $response = $this->json('PUT', "/api/v2/emplacements/{$a->id}", $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('emplacements', ['id' => $a->id, 'parent_id' => null]);
    }

    public function testDeleteEmplacementCascadesToHangarRow(): void
    {
        $couleur = Couleur::factory()->create();
        $emplacement = Emplacement::factory()->create(['couleur_id' => $couleur->id]);
        $localite = Localite::inRandomOrder()->first();
        Hangar::create(['id' => $emplacement->id, 'rue' => '', 'localite_id' => $localite->id]);

        $response = $this->json('DELETE', "/api/v2/emplacements/{$emplacement->id}", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('hangars', ['id' => $emplacement->id]);
    }
}
