<?php

namespace Tests\Feature;

use App\Infrastructure\Models\GroupeSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurGroupesTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexGroupesReturnsListOfGroupes(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        GroupeSapeur::factory()->forSapeur($sapeur->id)->forGroupe(1)->create();
        GroupeSapeur::factory()->forSapeur($sapeur->id)->forGroupe(2)->create();
        GroupeSapeur::factory()->forSapeur($sapeur->id)->forGroupe(3)->create();

        // Act
        $response = $this->json('GET', "/api/v2/sapeurs/{$sapeur->id}/groupes");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function testIndexGroupesReturnsErrorWhenSapeurNotFound(): void
    {
        // Act
        $response = $this->json('GET', '/api/v2/sapeurs/99999/groupes');

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    public function testQuitterGroupesSuccessfully(): void
    {
        // Arrange
        $sapeur = Sapeur::factory()->create();
        $groupe1 = GroupeSapeur::factory()->forSapeur($sapeur->id)->forGroupe(1)->create();
        $groupe2 = GroupeSapeur::factory()->forSapeur($sapeur->id)->forGroupe(2)->create();
        $groupe3 = GroupeSapeur::factory()->forSapeur($sapeur->id)->forGroupe(3)->create();

        // Act
        $response = $this->json('POST', "/api/v2/sapeurs/{$sapeur->id}/quitter-groupes", [
            'groupes' => [$groupe1->id, $groupe2->id]
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertDatabaseMissing('groupe_sapeur', ['id' => $groupe1->id]);
        $this->assertDatabaseMissing('groupe_sapeur', ['id' => $groupe2->id]);
        $this->assertDatabaseHas('groupe_sapeur', ['id' => $groupe3->id]);
    }

    public function testQuitterGroupesReturnsErrorWhenSapeurNotFound(): void
    {
        // Act
        $response = $this->json('POST', '/api/v2/sapeurs/99999/quitter-groupes', [
            'groupes' => [1, 2]
        ]);

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }
}
