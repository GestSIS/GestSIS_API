<?php

namespace Tests\Feature;

use App\Models\Couleur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CouleurTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexReturnsCouleursOrderedByNom(): void
    {
        Couleur::factory()->create(['nom' => 'Zinc']);
        Couleur::factory()->create(['nom' => 'Ambre']);

        $response = $this->json('GET', '/api/v2/couleurs');

        $response->assertStatus(200);
        $noms = array_column($response->json('data'), 'nom');
        $this->assertLessThan(array_search('Zinc', $noms), array_search('Ambre', $noms));
    }

    public function testStoreCreatesCouleur(): void
    {
        $response = $this->json('POST', '/api/v2/couleurs', [
            'nom' => 'Rouge intervention',
            'texte' => '#ffffff',
            'fond' => '#ff0000',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['data' => ['id', 'nom', 'texte', 'fond']]);
        $this->assertDatabaseHas('couleurs', ['nom' => 'Rouge intervention']);
    }

    public function testUpdateModifiesCouleur(): void
    {
        $couleur = Couleur::factory()->create(['nom' => 'Avant']);

        $response = $this->json('PUT', "/api/v2/couleurs/{$couleur->id}", [
            'nom' => 'Apres',
            'texte' => $couleur->texte,
            'fond' => $couleur->fond,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('couleurs', ['id' => $couleur->id, 'nom' => 'Apres']);
    }

    public function testDestroyDeletesCouleur(): void
    {
        $couleur = Couleur::factory()->create();

        $response = $this->json('DELETE', "/api/v2/couleurs/{$couleur->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('couleurs', ['id' => $couleur->id]);
    }
}
