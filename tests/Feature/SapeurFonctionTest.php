<?php

namespace Tests\Feature;

use App\Infrastructure\Models\FonctionSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurFonctionTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexFonctionsReturnsListOfFonctions(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        FonctionSapeur::factory()->forSapeur($sapeur->id)->ofFonction(1)->create();
        FonctionSapeur::factory()->forSapeur($sapeur->id)->ofFonction(2)->create();
        FonctionSapeur::factory()->forSapeur($sapeur->id)->ofFonction(3)->create();

        // Act
        $response = $this->json('GET', "/api/v2/sapeurs/{$sapeur->id}/fonctions");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function testIndexFonctionsReturnsErrorWhenSapeurNotFound(): void
    {
        // Act
        $response = $this->json('GET', '/api/v2/sapeurs/99999/fonctions');

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testAddFonctionSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $data = [
            'debut' => '1958-01-01',
            'fin' => '1958-09-17',
            'remarque' => 'Test remarque',
            'fonction_id' => 2
        ];

        // Act
        $response = $this->json('POST', "/api/v2/sapeurs/{$sapeur->id}/fonctions", $data);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['fonction' => ['id', 'fonction_id', 'sapeur_id', 'debut', 'fin']]
            ]);
        $this->assertDatabaseHas('fonction_sapeur', [
            'sapeur_id' => $sapeur->id,
            'fonction_id' => 2
        ]);
    }

    public function testAddFonctionReturnsErrorWhenSapeurNotFound(): void
    {
        // Arrange
        $data = [
            'debut' => '1958-01-01',
            'fin' => '1958-09-17',
            'remarque' => '',
            'fonction_id' => 2
        ];

        // Act
        $response = $this->json('POST', '/api/v2/sapeurs/99999/fonctions', $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testEditFonctionSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $fonction = FonctionSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofFonction(2)
            ->withDebut('1958-01-01')
            ->withFin('1958-09-17')
            ->create();

        $updateData = [
            'id' => $fonction->id,
            'debut' => '1959-05-08',
            'fin' => '1960-09-17',
            'remarque' => 'Updated'
        ];

        // Act
        $response = $this->json('PUT', "/api/v2/sapeurs/{$sapeur->id}/fonctions/{$fonction->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['fonction' => ['id', 'fonction_id', 'sapeur_id', 'debut', 'fin']]
            ]);
        $this->assertDatabaseHas('fonction_sapeur', [
            'id' => $fonction->id,
            'remarque' => 'Updated'
        ]);
    }

    public function testEditFonctionReturnsErrorWhenSapeurNotFound(): void
    {
        // Arrange
        $data = [
            'id' => 1,
            'debut' => '1959-05-08',
            'fin' => '1960-09-17',
            'remarque' => 'Test'
        ];

        // Act
        $response = $this->json('PUT', '/api/v2/sapeurs/99999/fonctions/1', $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testEditFonctionReturnsErrorWhenFonctionNotFound(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $data = [
            'id' => 99999,
            'debut' => '1959-05-08',
            'fin' => '1960-09-17',
            'remarque' => 'Test'
        ];

        // Act
        $response = $this->json('PUT', "/api/v2/sapeurs/{$sapeur->id}/fonctions/99999", $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Fonction non trouvée']);
    }

    public function testRemoveFonctionSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $fonction = FonctionSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofFonction(2)
            ->create();

        // Act
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/fonctions/{$fonction->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertDatabaseMissing('fonction_sapeur', ['id' => $fonction->id]);
    }

    public function testRemoveFonctionReturnsErrorWhenSapeurNotFound(): void
    {
        // Act
        $response = $this->json('DELETE', '/api/v2/sapeurs/99999/fonctions/1');

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testRemoveFonctionReturnsErrorWhenFonctionNotFound(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();

        // Act
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/fonctions/99999");

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Fonction non trouvée']);
    }

    public function testFinFonctionsSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $fonction1 = FonctionSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofFonction(2)
            ->withDebut('1958-01-01')
            ->withFin(null)
            ->create();
        $fonction2 = FonctionSapeur::factory()
            ->forSapeur($sapeur->id)
            ->ofFonction(3)
            ->withDebut('1959-01-01')
            ->withFin(null)
            ->create();

        $data = [
            'date' => '1960-09-17',
            'ids' => [$fonction1->id]
        ];

        // Act
        $response = $this->json('POST', "/api/v2/sapeurs/{$sapeur->id}/fin-fonctions", $data);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertDatabaseHas('fonction_sapeur', [
            'id' => $fonction1->id,
            'fin' => '1960-09-17'
        ]);
        // fonction2 should still have null fin
        $this->assertDatabaseHas('fonction_sapeur', [
            'id' => $fonction2->id,
            'fin' => null
        ]);
    }

    public function testFinFonctionsReturnsErrorWhenSapeurNotFound(): void
    {
        // Arrange
        $data = [
            'date' => '1960-09-17',
            'ids' => [1]
        ];

        // Act
        $response = $this->json('POST', '/api/v2/sapeurs/99999/fin-fonctions', $data);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }
}
