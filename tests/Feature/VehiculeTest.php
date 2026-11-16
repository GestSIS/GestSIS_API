<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
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

    private function creerVehicule(array $attributes = []): Article
    {
        $type = MaterielType::firstOrCreate(
            ['type' => MaterielTypeBusiness::TYPE_VEHICULE, 'est_emplacement' => true],
            MaterielType::factory()->make(['type' => MaterielTypeBusiness::TYPE_VEHICULE, 'est_emplacement' => true])->toArray(),
        );

        return Article::factory()->create(array_merge([
            'materiel_type_id' => $type->id,
            'emplacement_id' => null,
            'sapeur_id' => null,
        ], $attributes));
    }

    public function testVehiculesParDefautExclutSupprimesEtIndisponibles(): void
    {
        $actif = $this->creerVehicule();
        $indisponible = $this->creerVehicule(['disponible_intervention' => false]);
        $supprime = $this->creerVehicule(['supprime_le' => now()->toDateString()]);

        $ids = collect($this->json('GET', '/api/v2/vehicules', [], ['Sis-Key' => 1])
            ->assertStatus(200)->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($actif->id));
        $this->assertFalse($ids->contains($indisponible->id));
        $this->assertFalse($ids->contains($supprime->id));
    }

    public function testVehiculesTousRenvoieTout(): void
    {
        $actif = $this->creerVehicule();
        $indisponible = $this->creerVehicule(['disponible_intervention' => false]);
        $supprime = $this->creerVehicule(['supprime_le' => now()->toDateString()]);

        $data = collect($this->json('GET', '/api/v2/vehicules?tous=1', [], ['Sis-Key' => 1])
            ->assertStatus(200)->json('data'));

        $this->assertTrue($data->pluck('id')->contains($actif->id));
        $this->assertFalse($data->firstWhere('id', $indisponible->id)['disponible_intervention']);
        $this->assertNotNull($data->firstWhere('id', $supprime->id)['supprime_le']);
    }
}
