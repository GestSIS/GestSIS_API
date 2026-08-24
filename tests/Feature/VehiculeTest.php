<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Couleur;
use App\Models\Emplacement;
use App\Models\MaterielType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehiculeTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexReturnsVehiculeArticlesWithEmplacementRepresentee(): void
    {
        $type = MaterielType::factory()->create([
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_emplacement' => true,
        ]);
        $parent = Emplacement::factory()->create();
        $couleur = Couleur::factory()->create();

        $response = $this->json('POST', '/api/v2/articles', [
            'articles' => [[
                'materiel_type_id' => $type->id,
                'quantite' => 1,
                'designation' => 'Camion 1',
                'emplacement' => [
                    'couleur_id' => $couleur->id,
                    'parent_id' => $parent->id,
                ],
            ]],
        ], ['Sis-Key' => 1]);
        $response->assertStatus(200);
        $articleId = $response->json('data.0.id');

        $response = $this->json('GET', '/api/v2/vehicules', [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $vehicule = collect($response->json('data'))->firstWhere('id', $articleId);
        $this->assertNotNull($vehicule);
        $this->assertSame($couleur->id, $vehicule['emplacement_representee']['couleur_id']);
        $this->assertSame($parent->id, $vehicule['emplacement_representee']['parent_id']);
    }
}
