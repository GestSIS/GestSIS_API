<?php

namespace Tests\Feature;

use App\Infrastructure\Models\GradeSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurGradeTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexGradesReturnsListOfGrades(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        GradeSapeur::factory()->forSapeur($sapeur->id)->ofGrade(1)->create();
        GradeSapeur::factory()->forSapeur($sapeur->id)->ofGrade(2)->create();
        GradeSapeur::factory()->forSapeur($sapeur->id)->ofGrade(3)->create();

        // Act
        $response = $this->json('GET', "/api/v2/sapeurs/{$sapeur->id}/grades");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function testIndexGradesReturnsErrorWhenSapeurNotFound(): void
    {
        // Act
        $response = $this->json('GET', '/api/v2/sapeurs/99999/grades');

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testAddGradeSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $data = [
            'date' => '1958-01-01',
            'remarque' => 'Test remarque',
            'grade_id' => 2
        ];

        // Act
        $response = $this->json('POST', "/api/v2/sapeurs/{$sapeur->id}/grades", $data);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['grade' => ['id', 'grade_id', 'sapeur_id', 'date']]
            ]);
        $this->assertDatabaseHas('grade_sapeur', [
            'sapeur_id' => $sapeur->id,
            'grade_id' => 2
        ]);
    }

    public function testAddGradeReturnsErrorWhenSapeurNotFound(): void
    {
        // Arrange
        $data = [
            'date' => '1958-01-01',
            'remarque' => '',
            'grade_id' => 2
        ];

        // Act
        $response = $this->json('POST', '/api/v2/sapeurs/99999/grades', $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testEditGradeSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $grade = GradeSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofGrade(4)
            ->withDate('1958-01-01')
            ->create();

        $updateData = [
            'id' => $grade->id,
            'date' => '1959-05-08',
            'remarque' => 'Deserve it'
        ];

        // Act
        $response = $this->json('PUT', "/api/v2/sapeurs/{$sapeur->id}/grades/{$grade->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['grade' => ['id', 'grade_id', 'sapeur_id', 'date']]
            ]);
        $this->assertDatabaseHas('grade_sapeur', [
            'id' => $grade->id,
            'remarque' => 'Deserve it'
        ]);
    }

    public function testEditGradeReturnsErrorWhenSapeurNotFound(): void
    {
        // Arrange
        $data = [
            'id' => 1,
            'date' => '1959-05-08',
            'remarque' => 'Test'
        ];

        // Act
        $response = $this->json('PUT', '/api/v2/sapeurs/99999/grades/1', $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testEditGradeReturnsErrorWhenGradeNotFound(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $data = [
            'id' => 99999,
            'date' => '1959-05-08',
            'remarque' => 'Test'
        ];

        // Act
        $response = $this->json('PUT', "/api/v2/sapeurs/{$sapeur->id}/grades/99999", $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Grade non trouvé']);
    }

    public function testRemoveGradeSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $grade = GradeSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofGrade(5)
            ->create();

        // Act
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/grades/{$grade->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertDatabaseMissing('grade_sapeur', ['id' => $grade->id]);
    }

    public function testRemoveGradeReturnsErrorWhenSapeurNotFound(): void
    {
        // Act
        $response = $this->json('DELETE', '/api/v2/sapeurs/99999/grades/1');

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testRemoveGradeReturnsErrorWhenGradeNotFound(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();

        // Act
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/grades/99999");

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Grade non trouvé']);
    }
}
