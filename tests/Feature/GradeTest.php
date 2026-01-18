<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Grade;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexGradesReturnsListOfGrades(): void
    {
        Grade::factory()->count(3)->create();

        $response = $this->json('GET', '/api/v2/grades/', [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'designation',
                    'abreviation',
                    'groupe',
                    'tri',
                ],
            ],
        ]);
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function testStoreGradeSuccessfully(): void
    {
        $gradeData = [
            'designation' => 'Test Grade',
            'abreviation' => 'TG',
            'groupe' => 2,
            'tri' => 10,
        ];

        $response = $this->json('POST', '/api/v2/grades/', $gradeData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'designation',
                'abreviation',
                'groupe',
                'tri',
            ],
        ]);
        $this->assertEquals('Test Grade', $response->json('data.designation'));
        $this->assertEquals('TG', $response->json('data.abreviation'));

        $this->assertDatabaseHas('grades', [
            'designation' => 'Test Grade',
            'abreviation' => 'TG',
            'groupe' => 2,
        ]);
    }

    public function testUpdateGradeSuccessfully(): void
    {
        $grade = Grade::factory()->create([
            'designation' => 'Original Grade',
            'abreviation' => 'OG',
        ]);

        $updateData = [
            'designation' => 'Updated Grade',
            'abreviation' => 'UG',
            'groupe' => 3,
            'tri' => 20,
        ];

        $response = $this->json('PUT', '/api/v2/grades/' . $grade->id, $updateData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Grade', $response->json('data.designation'));
        $this->assertEquals('UG', $response->json('data.abreviation'));

        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'designation' => 'Updated Grade',
            'abreviation' => 'UG',
        ]);
    }

    public function testUpdateGradeReturnsErrorWhenGradeNotFound(): void
    {
        $updateData = [
            'designation' => 'Updated Grade',
            'abreviation' => 'UG',
            'groupe' => 3,
            'tri' => 20,
        ];

        $response = $this->json('PUT', '/api/v2/grades/99999', $updateData, [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Grade not found']);
    }

    public function testDestroyGradeSuccessfully(): void
    {
        $grade = Grade::factory()->create([
            'designation' => 'Grade to Delete',
        ]);

        $response = $this->json('DELETE', '/api/v2/grades/' . $grade->id, [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('grades', [
            'id' => $grade->id,
        ]);
    }

    public function testDestroyGradeReturnsErrorWhenGradeNotFound(): void
    {
        $response = $this->json('DELETE', '/api/v2/grades/99999', [], [
            'Sis-Id' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Grade not found']);
    }
}
