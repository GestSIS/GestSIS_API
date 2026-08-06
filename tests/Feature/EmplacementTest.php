<?php

namespace Tests\Feature;

use App\Models\Couleur;
use App\Models\Emplacement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmplacementTest extends TestCase
{
    use DatabaseTransactions;

    public function testStoreEmplacementComputesTriFromEmplacementsTable(): void
    {
        $couleur = Couleur::factory()->create();
        $maxBefore = (int) (Emplacement::max('id') ?? 0);

        $response = $this->json('POST', '/api/v2/emplacements', [
            'designation' => 'Armoire A',
            'est_etiquete' => false,
            'est_compartimentable' => false,
            'couleur_id' => $couleur->id,
            'parent_id' => null,
            'statut' => true,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        // tri doit être calculé sur la table emplacements (et non materiel_categories)
        $this->assertSame($maxBefore + 1, $response->json('data.tri'));
        $this->assertDatabaseHas('emplacements', [
            'id' => $response->json('data.id'),
            'designation' => 'Armoire A',
            'tri' => $maxBefore + 1,
        ]);
    }
}
