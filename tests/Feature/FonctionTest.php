<?php

namespace Tests\Feature;

use App\Models\Fonction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FonctionTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexFonctionsReturnsListOfFonctions(): void
    {
        Fonction::factory()->count(3)->create();

        $response = $this->json('GET', '/api/v2/fonctions/', [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nom',
                    'abreviation',
                    'tri',
                    'cumulable',
                    'actif',
                ],
            ],
        ]);
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function testStoreFonctionSuccessfully(): void
    {
        $fonctionData = [
            'nom' => 'Test Fonction',
            'abreviation' => 'TF',
            'tri' => 10,
            'cumulable' => true,
            'actif' => true,
        ];

        $response = $this->json('POST', '/api/v2/fonctions/', $fonctionData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'nom',
                'abreviation',
                'tri',
                'cumulable',
                'actif',
            ],
        ]);
        $this->assertEquals('Test Fonction', $response->json('data.nom'));
        $this->assertEquals('TF', $response->json('data.abreviation'));

        $this->assertDatabaseHas('fonctions', [
            'nom' => 'Test Fonction',
            'abreviation' => 'TF',
            'cumulable' => true,
            'actif' => true,
        ]);
    }

    public function testUpdateFonctionSuccessfully(): void
    {
        $fonction = Fonction::factory()->create([
            'nom' => 'Original Fonction',
            'abreviation' => 'OF',
        ]);

        $updateData = [
            'nom' => 'Updated Fonction',
            'abreviation' => 'UF',
            'tri' => 20,
            'cumulable' => false,
            'actif' => true,
        ];

        $response = $this->json('PUT', '/api/v2/fonctions/' . $fonction->id, $updateData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Fonction', $response->json('data.nom'));
        $this->assertEquals('UF', $response->json('data.abreviation'));

        $this->assertDatabaseHas('fonctions', [
            'id' => $fonction->id,
            'nom' => 'Updated Fonction',
            'abreviation' => 'UF',
        ]);
    }

    public function testUpdateFonctionReturnsErrorWhenFonctionNotFound(): void
    {
        $updateData = [
            'nom' => 'Updated Fonction',
            'abreviation' => 'UF',
            'tri' => 20,
            'cumulable' => false,
            'actif' => true,
        ];

        $response = $this->json('PUT', '/api/v2/fonctions/99999', $updateData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Fonction not found']);
    }

    public function testDestroyFonctionSuccessfully(): void
    {
        $fonction = Fonction::factory()->create([
            'nom' => 'Fonction to Delete',
        ]);

        $response = $this->json('DELETE', '/api/v2/fonctions/' . $fonction->id, [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('fonctions', [
            'id' => $fonction->id,
        ]);
    }

    public function testDestroyFonctionReturnsErrorWhenFonctionNotFound(): void
    {
        $response = $this->json('DELETE', '/api/v2/fonctions/99999', [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Fonction not found']);
    }
}
