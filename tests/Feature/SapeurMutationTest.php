<?php

namespace Tests\Feature;

use App\Models\Localite;
use App\Models\Sapeur;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurMutationTest extends TestCase
{
    use DatabaseTransactions;

    protected $localiteId;
    protected $sapeurId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create localite fixture
        $localite = Localite::firstOrCreate(
            ['id' => 1],
            [
                'commune_id' => null,
                'npa' => '2800',
                'designation' => 'Test Localité',
            ]
        );

        $this->localiteId = $localite->id;

        // Create sapeur fixture for mutation tests
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $this->sapeurId = $sapeur->id;
    }

    public function testIndexMutationsReturnsListOfMutations()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/{$this->sapeurId}/mutations");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'incorporation',
                        'sapeur_id',
                        'sortie',
                        'motif'
                    ]
                ]
            ]);

        // Verify the factory-created mutation is present
        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($responseData));
    }

    public function testIndexMutationsReturnsErrorWhenSapeurNotFound()
    {
        $response = $this->json('GET', '/api/v2/sapeurs/999999/mutations');

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function testAddMutationSuccessfully()
    {
        // Use dates BEFORE the initial mutation (2019-01-29) to avoid overlap
        $data = [
            'incorporation' => "2005-01-01",
            'sortie' => "2008-12-31",
            'motif' => 'Test mutation',
            'localite_id' => $this->localiteId
        ];

        $response = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'mutation' => ['id', 'incorporation', 'sapeur_id', 'sortie', 'motif'],
                    'actif'
                ]
            ])
            ->assertJsonPath('data.mutation.sapeur_id', $this->sapeurId)
            ->assertJsonPath('data.mutation.motif', $data['motif'])
            ->assertJsonPath('data.mutation.localite_id', $data['localite_id']);

        // Verify incorporation date (JSON returns ISO format)
        $mutationIncorporation = Carbon::parse($response->json('data.mutation.incorporation'));
        $expectedIncorporation = Carbon::parse($data['incorporation']);
        $this->assertTrue($mutationIncorporation->isSameDay($expectedIncorporation));

        // Verify the mutation exists via GET
        $mutationId = $response->json('data.mutation.id');
        $getResponse = $this->json('GET', "/api/v2/sapeurs/{$this->sapeurId}/mutations");
        $mutations = $getResponse->json('data');
        $this->assertContains($mutationId, array_column($mutations, 'id'));
    }

    public function testEditMutationSuccessfully()
    {
        // First, create a mutation with dates BEFORE the initial one (2019)
        $createData = [
            'incorporation' => "2010-01-01",
            'sortie' => "2014-12-31",
            'motif' => 'Original mutation',
            'localite_id' => $this->localiteId
        ];

        $createResponse = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", $createData);
        $mutationId = $createResponse->json('data.mutation.id');

        // Now update it (keeping dates non-overlapping with other mutations)
        $updateData = [
            'incorporation' => "2007-01-01",
            'sortie' => "2009-12-31",
            'motif' => 'Updated mutation',
            'localite_id' => $this->localiteId,
            'id' => $mutationId
        ];

        $response = $this->json('PUT', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}", $updateData);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'mutation' => ['id', 'localite_id', 'incorporation', 'sapeur_id', 'sortie', 'motif'],
                    'actif'
                ]
            ])
            ->assertJsonPath('data.mutation.id', $mutationId)
            ->assertJsonPath('data.mutation.motif', $updateData['motif'])
            ->assertJsonPath('data.mutation.localite_id', $updateData['localite_id']);

        // Verify dates
        $mutationIncorporation = Carbon::parse($response->json('data.mutation.incorporation'));
        $expectedIncorporation = Carbon::parse($updateData['incorporation']);
        $this->assertTrue($mutationIncorporation->isSameDay($expectedIncorporation));

        $mutationSortie = Carbon::parse($response->json('data.mutation.sortie'));
        $expectedSortie = Carbon::parse($updateData['sortie']);
        $this->assertTrue($mutationSortie->isSameDay($expectedSortie));
    }

    public function testEditMutationReturnsErrorWhenNotFound()
    {
        $updateData = [
            'incorporation' => "2022-01-16",
            'sortie' => null,
            'motif' => 'Updated mutation',
            'localite_id' => $this->localiteId,
            'id' => 999999
        ];

        $response = $this->json('PUT', "/api/v2/sapeurs/{$this->sapeurId}/mutations/999999", $updateData);

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function testRemoveMutationSuccessfully()
    {
        // First, create a mutation to delete with non-overlapping dates (before 2019)
        // Use dates well before the factory mutation (2019-01-29)
        $createData = [
            'incorporation' => "2012-01-01",
            'sortie' => "2014-12-31",
            'motif' => 'To be deleted',
            'localite_id' => $this->localiteId
        ];

        $createResponse = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", $createData);
        $mutationId = $createResponse->json('data.mutation.id');

        // Delete it
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        // Verify it's deleted via GET
        $getResponse = $this->json('GET', "/api/v2/sapeurs/{$this->sapeurId}/mutations");
        $mutations = $getResponse->json('data');
        $this->assertNotContains($mutationId, array_column($mutations, 'id'));
    }

    public function testRemoveMutationReturnsErrorWhenNotFound()
    {
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$this->sapeurId}/mutations/999999");

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }
}