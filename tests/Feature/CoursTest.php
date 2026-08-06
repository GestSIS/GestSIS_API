<?php

namespace Tests\Feature;

use App\Models\Cours;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CoursTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexCoursReturnsListOfCours(): void
    {
        Cours::factory()->count(3)->create();

        $response = $this->json('GET', '/api/v2/cours/', [], [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'designation',
                    'abreviation',
                    'tri',
                    'duree',
                ],
            ],
        ]);
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function testStoreCoursSuccessfully(): void
    {
        $coursData = [
            'designation' => 'Test Cours',
            'abreviation' => 'TC',
            'tri' => 10,
            'duree' => 16.5,
            'validite_debut' => null,
            'validite_fin' => null,
            'fonction_id' => null,
            'grade_id' => null,
            'precedent_id' => null,
        ];

        $response = $this->json('POST', '/api/v2/cours/', $coursData, [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'designation',
                'abreviation',
                'tri',
                'duree',
            ],
        ]);
        $this->assertEquals('Test Cours', $response->json('data.designation'));
        $this->assertEquals('TC', $response->json('data.abreviation'));

        $this->assertDatabaseHas('cours', [
            'designation' => 'Test Cours',
            'abreviation' => 'TC',
        ]);
    }

    public function testUpdateCoursSuccessfully(): void
    {
        $cours = Cours::factory()->create([
            'designation' => 'Original Cours',
            'abreviation' => 'OC',
        ]);

        $updateData = [
            'designation' => 'Updated Cours',
            'abreviation' => 'UC',
            'tri' => 20,
            'duree' => 24.0,
            'validite_debut' => null,
            'validite_fin' => null,
            'fonction_id' => null,
            'grade_id' => null,
            'precedent_id' => null,
        ];

        $response = $this->json('PUT', '/api/v2/cours/' . $cours->id, $updateData, [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Cours', $response->json('data.designation'));
        $this->assertEquals('UC', $response->json('data.abreviation'));

        $this->assertDatabaseHas('cours', [
            'id' => $cours->id,
            'designation' => 'Updated Cours',
            'abreviation' => 'UC',
        ]);
    }

    public function testUpdateCoursReturnsErrorWhenCoursNotFound(): void
    {
        $updateData = [
            'designation' => 'Updated Cours',
            'abreviation' => 'UC',
            'tri' => 20,
            'duree' => 24.0,
            'validite_debut' => null,
            'validite_fin' => null,
            'fonction_id' => null,
            'grade_id' => null,
            'precedent_id' => null,
        ];

        $response = $this->json('PUT', '/api/v2/cours/99999', $updateData, [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Cours not found']);
    }

    public function testDestroyCoursSuccessfully(): void
    {
        $cours = Cours::factory()->create([
            'designation' => 'Cours to Delete',
        ]);

        $response = $this->json('DELETE', '/api/v2/cours/' . $cours->id, [], [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('cours', [
            'id' => $cours->id,
        ]);
    }

    public function testDestroyCoursReturnsErrorWhenCoursNotFound(): void
    {
        $response = $this->json('DELETE', '/api/v2/cours/99999', [], [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Cours not found']);
    }
}
