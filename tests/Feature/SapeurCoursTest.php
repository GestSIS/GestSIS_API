<?php

namespace Tests\Feature;

use App\Infrastructure\Models\CoursSapeur;
use App\Infrastructure\Models\FonctionSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurCoursTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexCoursReturnsListOfCours(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        CoursSapeur::factory()->forSapeur($sapeur->id)->ofCours(1)->create();
        CoursSapeur::factory()->forSapeur($sapeur->id)->ofCours(2)->create();
        CoursSapeur::factory()->forSapeur($sapeur->id)->ofCours(3)->create();

        // Act
        $response = $this->json('GET', "/api/v2/sapeurs/{$sapeur->id}/cours");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function testIndexCoursReturnsErrorWhenSapeurNotFound(): void
    {
        // Act
        $response = $this->json('GET', '/api/v2/sapeurs/99999/cours');

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testAddCoursSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $data = [
            'date' => '1958-02-07',
            'duree' => 1,
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => null,
            'fonction_id' => null,
            'fonction_sapeur_id' => null,
        ];

        // Act
        $response = $this->json('POST', "/api/v2/sapeurs/{$sapeur->id}/cours", $data);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['cours' => ['id', 'sapeur_id', 'cours_id', 'date', 'localite_id']]
            ]);
        $this->assertDatabaseHas('cours_sapeur', [
            'sapeur_id' => $sapeur->id,
            'cours_id' => 2
        ]);
    }

    public function testAddCoursWithGradeAndFonctionCreatesAssociatedRecords(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $fonctionExistante = FonctionSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofFonction(2)
            ->withDebut('1958-01-01')
            ->withFin(null)
            ->create();

        $data = [
            'date' => '1958-02-07',
            'duree' => 1,
            'date_fonction' => '1960-06-05',
            'date_grade' => '1965-12-29',
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => 5,
            'fonction_id' => 14,
            'fonction_sapeur_id' => $fonctionExistante->id,
        ];

        // Act
        $response = $this->json('POST', "/api/v2/sapeurs/{$sapeur->id}/cours", $data);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['cours' => ['id', 'sapeur_id', 'cours_id', 'date', 'localite_id']]
            ]);

        // Verify cours was created
        $this->assertDatabaseHas('cours_sapeur', [
            'sapeur_id' => $sapeur->id,
            'cours_id' => 2
        ]);

        // Verify grade was created
        $this->assertDatabaseHas('grade_sapeur', [
            'sapeur_id' => $sapeur->id,
            'grade_id' => 5,
            'date' => '1965-12-29'
        ]);

        // Verify old fonction was terminated
        $this->assertDatabaseHas('fonction_sapeur', [
            'id' => $fonctionExistante->id,
            'fin' => '1960-06-05'
        ]);

        // Verify new fonction was created
        $this->assertDatabaseHas('fonction_sapeur', [
            'sapeur_id' => $sapeur->id,
            'fonction_id' => 14,
            'debut' => '1960-06-05'
        ]);
    }

    public function testAddCoursReturnsErrorWhenSapeurNotFound(): void
    {
        // Arrange
        $data = [
            'date' => '1958-02-07',
            'duree' => 1,
            'localite_id' => 1,
            'cours_id' => 2,
        ];

        // Act
        $response = $this->json('POST', '/api/v2/sapeurs/99999/cours', $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testEditCoursSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $cours = CoursSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofCours(2)
            ->withDate('1958-01-01')
            ->withLocalite(1)
            ->withDuree(1)
            ->create();

        $updateData = [
            'id' => $cours->id,
            'date' => '1958-01-01',
            'localite_id' => 2,
        ];

        // Act
        $response = $this->json('PUT', "/api/v2/sapeurs/{$sapeur->id}/cours/{$cours->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'sapeur_id', 'cours_id', 'date', 'localite_id']
            ]);
        $this->assertDatabaseHas('cours_sapeur', [
            'id' => $cours->id,
            'localite_id' => 2
        ]);
    }

    public function testEditCoursReturnsErrorWhenSapeurNotFound(): void
    {
        // Arrange
        $data = [
            'id' => 1,
            'date' => '1958-01-01',
            'localite_id' => 2,
        ];

        // Act
        $response = $this->json('PUT', '/api/v2/sapeurs/99999/cours/1', $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testEditCoursReturnsErrorWhenCoursNotFound(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $data = [
            'id' => 99999,
            'date' => '1958-01-01',
            'localite_id' => 2,
        ];

        // Act
        $response = $this->json('PUT', "/api/v2/sapeurs/{$sapeur->id}/cours/99999", $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Cours non trouvé']);
    }

    public function testRemoveCoursSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $cours = CoursSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofCours(2)
            ->create();

        // Act
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/cours/{$cours->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertDatabaseMissing('cours_sapeur', ['id' => $cours->id]);
    }

    public function testRemoveCoursReturnsErrorWhenSapeurNotFound(): void
    {
        // Act
        $response = $this->json('DELETE', '/api/v2/sapeurs/99999/cours/1');

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testRemoveCoursReturnsErrorWhenCoursNotFound(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();

        // Act
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/cours/99999");

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Cours non trouvé']);
    }
}
