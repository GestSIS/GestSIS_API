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
        GroupeSapeur::factory()->create(['sapeur_id' => $sapeur->id, 'groupe_id' => 1]);
        GroupeSapeur::factory()->create(['sapeur_id' => $sapeur->id, 'groupe_id' => 2]);
        GroupeSapeur::factory()->create(['sapeur_id' => $sapeur->id, 'groupe_id' => 3]);

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
        $groupe1 = GroupeSapeur::factory()->create(['sapeur_id' => $sapeur->id, 'groupe_id' => 1]);
        $groupe2 = GroupeSapeur::factory()->create(['sapeur_id' => $sapeur->id, 'groupe_id' => 2]);
        $groupe3 = GroupeSapeur::factory()->create(['sapeur_id' => $sapeur->id, 'groupe_id' => 3]);

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
