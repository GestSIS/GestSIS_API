<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Localite;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurTest extends TestCase
{
    use DatabaseTransactions;

    protected $localiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $localite = Localite::firstOrCreate(
            ['id' => 1],
            [
                'commune_id' => null,
                'npa' => '2800',
                'designation' => 'Test Localité',
            ]
        );

        $this->localiteId = $localite->id;
    }

    public function testIndexSapeurReturnsListOfSapeurs()
    {
        $sapeur1 = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $sapeur2 = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nom', 'prenom', 'fonction_id', 'actif']
                ]
            ]);

        // Check that our created sapeurs are in the response
        $responseData = $response->json('data');
        $this->assertContains($sapeur1->id, array_column($responseData, 'id'));
        $this->assertContains($sapeur2->id, array_column($responseData, 'id'));
    }

    public function testShowSapeurReturnsSpecificSapeur()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nom',
                    'prenom',
                    'fonction_id',
                    'actif',
                    'date_naissance'
                ]
            ])
            ->assertJsonPath('data.id', $sapeur->id)
            ->assertJsonPath('data.nom', $sapeur->nom)
            ->assertJsonPath('data.prenom', $sapeur->prenom);
    }

    public function testShowSapeurReturnsErrorWhenNotFound()
    {
        $response = $this->json('GET', '/api/v2/sapeurs/999999');

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function testCreateSapeurSuccessfully()
    {
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29"; // ISO format
        $data['localite_id'] = $this->localiteId; // Use existing localite

        $response = $this->json('POST', '/api/v2/sapeurs', $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nom',
                    'prenom',
                    'date_naissance'
                ]
            ])
            ->assertJsonPath('data.nom', $data['nom'])
            ->assertJsonPath('data.prenom', $data['prenom'])
            ->assertJsonPath('data.fonction_id', 0);

        $sapeurId = $response->json('data.id');
        $this->assertNotNull($sapeurId);

        // Verify mutation was created via REST API
        $mutationsResponse = $this->json('GET', '/api/v2/sapeurs/' . $sapeurId . '/mutations');
        $mutationsResponse->assertStatus(200);
        $this->assertCount(1, $mutationsResponse->json('data'));
    }

    public function testUpdateSapeurSuccessfully()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        // Prepare update data
        $updateData = Sapeur::factory()->make()->toArray();
        $updateData['localite_id'] = $this->localiteId;

        $response = $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id, $updateData);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nom',
                    'prenom',
                    'date_naissance'
                ]
            ])
            ->assertJsonPath('data.id', $sapeur->id)
            ->assertJsonPath('data.nom', $updateData['nom'])
            ->assertJsonPath('data.prenom', $updateData['prenom']);

        // Verify the sapeur was actually updated via REST API
        $getResponse = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id);
        $getResponse->assertStatus(200);
        $this->assertEquals($updateData['nom'], $getResponse->json('data.nom'));
        $this->assertEquals($updateData['prenom'], $getResponse->json('data.prenom'));
    }

    public function testUpdateSapeurReturnsErrorWhenNotFound()
    {
        $data = Sapeur::factory()->make()->toArray();

        $response = $this->json('PUT', '/api/v2/sapeurs/99999999', $data);

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function testDestroySapeurSuccessfully()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('DELETE', '/api/v2/sapeurs/' . $sapeur->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        // Verify the sapeur was actually deleted
        $getResponse = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id);
        $getResponse->assertStatus(404);
    }

    public function testDestroySapeurReturnsErrorWhenNotFound()
    {
        $response = $this->json('DELETE', '/api/v2/sapeurs/99999999');

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function testEffectifReturnsActiveFirefighters()
    {
        // Create active sapeur
        $sapeurActif = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'actif' => 1,
            'type' => 0, // TYPE_SAPEUR
        ]);

        // Create inactive sapeur (should not appear)
        $sapeurInactif = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'actif' => 0,
            'type' => 0,
        ]);

        // Create active civil (should not appear)
        $civil = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'actif' => 1,
            'type' => 1, // TYPE_CIVIL
        ]);

        $response = $this->json('GET', '/api/v2/effectif');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nom', 'prenom', 'email']
                ]
            ]);

        $responseData = $response->json('data');
        $ids = array_column($responseData, 'id');

        // Verify only active sapeurs are returned
        $this->assertContains($sapeurActif->id, $ids);
        $this->assertNotContains($sapeurInactif->id, $ids);
        $this->assertNotContains($civil->id, $ids);
    }

    public function testAutreStatutUpdatesNonSapeurStatus()
    {
        // Create a civil (type=1), not a sapeur (type=0)
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'actif' => 0,
            'type' => 1, // TYPE_CIVIL
        ]);

        $response = $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id . '/autre-statut', [
            'actif' => 1,
        ]);

        $response->assertStatus(200);

        // Verify status was updated in database
        $sapeur->refresh();
        $this->assertEquals(1, $sapeur->actif);
    }

    public function testTrombinoscopeReturnsResponse()
    {
        $this->markTestSkipped('Fiche requires Sis-Key header and Trombinoscope requires Typst PDF generation');

        // Create some sapeurs for the trombinoscope
        Sapeur::factory()->count(3)->create([
            'localite_id' => $this->localiteId,
            'actif' => 1,
        ]);

        $response = $this->json('GET', '/api/v2/trombinoscope');

        // Should return a response (PDF or error structure)
        $response->assertStatus(200);
    }

    public function testFicheReturnsResponseForSapeur()
    {
        $this->markTestSkipped('Fiche requires Sis-Key header and Typst PDF generation');

        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id . '/fiche');

        // Should return a response (PDF or error structure)
        $response->assertStatus(200);
    }

    public function testListeFsspReturnsResponse()
    {
        Sapeur::factory()->count(2)->create([
            'localite_id' => $this->localiteId,
            'actif' => 1,
        ]);

        $response = $this->json('GET', '/api/v2/liste-fssp');

        $response->assertStatus(200);
    }

    public function testListeFoadReturnsResponse()
    {
        Sapeur::factory()->count(2)->create([
            'localite_id' => $this->localiteId,
            'actif' => 1,
        ]);

        $response = $this->json('GET', '/api/v2/liste-foad');

        $response->assertStatus(200);
    }

    public function testSapeursTelephonesReturnsData()
    {
        Sapeur::factory()->count(2)->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs-telephones');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function testConvocationSmsReturnsData()
    {
        Sapeur::factory()->count(2)->create([
            'localite_id' => $this->localiteId,
            'actif' => 1,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs-convocation');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
