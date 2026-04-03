<?php

namespace Tests\Feature;

use App\Models\Groupe;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GroupeTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexGroupesReturnsListOfGroupes(): void
    {
        Groupe::factory()->count(3)->create();

        $response = $this->json('GET', '/api/v2/groupes/', [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'designation',
                    'no',
                    'tri',
                    'type',
                    'parent_id',
                ],
            ],
        ]);
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function testStoreGroupeSuccessfully(): void
    {
        $groupeData = [
            'designation' => 'Test Groupe',
            'no' => '100',
            'tri' => 10,
            'type' => 1,
            'parent_id' => null,
        ];

        $response = $this->json('POST', '/api/v2/groupes/', $groupeData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'designation',
                'no',
                'tri',
                'type',
                'parent_id',
            ],
        ]);
        $this->assertEquals('Test Groupe', $response->json('data.designation'));
        $this->assertEquals('100', $response->json('data.no'));

        $this->assertDatabaseHas('groupes', [
            'designation' => 'Test Groupe',
            'no' => '100',
            'type' => 1,
        ]);
    }

    public function testUpdateGroupeSuccessfully(): void
    {
        $groupe = Groupe::factory()->create([
            'designation' => 'Original Groupe',
            'no' => '100',
        ]);

        $updateData = [
            'designation' => 'Updated Groupe',
            'no' => '200',
            'tri' => 20,
            'type' => 2,
            'parent_id' => null,
        ];

        $response = $this->json('PUT', '/api/v2/groupes/' . $groupe->id, $updateData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Groupe', $response->json('data.designation'));
        $this->assertEquals('200', $response->json('data.no'));

        $this->assertDatabaseHas('groupes', [
            'id' => $groupe->id,
            'designation' => 'Updated Groupe',
            'no' => '200',
        ]);
    }

    public function testUpdateGroupeReturnsErrorWhenGroupeNotFound(): void
    {
        $updateData = [
            'designation' => 'Updated Groupe',
            'no' => 'G-002',
            'tri' => 20,
            'type' => 2,
            'parent_id' => null,
        ];

        $response = $this->json('PUT', '/api/v2/groupes/99999', $updateData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Groupe not found']);
    }

    public function testDestroyGroupeSuccessfully(): void
    {
        $groupe = Groupe::factory()->create([
            'designation' => 'Groupe to Delete',
        ]);

        $response = $this->json('DELETE', '/api/v2/groupes/' . $groupe->id, [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => 'ok']);

        $this->assertDatabaseMissing('groupes', [
            'id' => $groupe->id,
        ]);
    }

    public function testDestroyGroupeReturnsErrorWhenGroupeNotFound(): void
    {
        $response = $this->json('DELETE', '/api/v2/groupes/99999', [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Groupe not found']);
    }
}

