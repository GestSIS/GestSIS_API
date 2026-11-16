<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
use App\Models\Intervention;
use App\Models\MaterielType;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionVehiculeTest extends TestCase
{
    use DatabaseTransactions;

    protected $interventionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->interventionId = $this->json('POST', '/api/v2/interventions', Intervention::factory()->make()->toArray())
            ->json('data.id');
    }

    /**
     * Test index intervention
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionVehiculeIndexOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/393/vehicules");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'intervention_id',
                        'id',
                        'vehicule_id'
                    ]
                ]
            ]);
    }

    /**
     * Test add vehicule
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionVehicule()
    {
        $vehicules = [1, 2, 3, 5];

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', ['vehicules' => $vehicules]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => true
            ]);
    }


    /**
     * Test add vehicule
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionVehiculeDuplicated()
    {
        $vehicules = [1, 2, 3, 5];

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', ['vehicules' => $vehicules]);
        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', ['vehicules' => $vehicules]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => true
            ])
            ->assertJsonCount(
                count($vehicules),
                'data'
            );
    }

    /**
     * Test remove vehicule
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveInterventionVehicule()
    {
        $vehiculesAdd = [1, 2, 3, 5];
        $vehiculesRemove = [1, 5];

        $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', ['vehicules' => $vehiculesAdd]);

        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/vehicules/', ["vehicules" => $vehiculesRemove]);

        $response->assertStatus(204);
    }

    /**
     * Véhicule (article d'un type véhicule) avec les attributs donnés.
     *
     * @param array<string, mixed> $extra
     */
    private function vehicule(array $extra = []): Article
    {
        $type = MaterielType::factory()->create([
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_emplacement' => true,
        ]);

        return Article::factory()->create(array_merge(['materiel_type_id' => $type->id], $extra));
    }

    public function testAddInterventionVehiculeSupprimeEstRefuse(): void
    {
        $vehicule = $this->vehicule(['supprime_le' => '2026-01-01']);

        $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', ['vehicules' => [$vehicule->id]], ['Sis-Key' => 1])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('intervention_vehicule', [
            'intervention_id' => $this->interventionId,
            'vehicule_id' => $vehicule->id,
        ]);
    }

    public function testAddInterventionVehiculeIndisponibleEstAccepte(): void
    {
        $vehicule = $this->vehicule(['disponible_intervention' => false]);

        $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', ['vehicules' => [$vehicule->id]], ['Sis-Key' => 1])
            ->assertStatus(201);

        $this->assertDatabaseHas('intervention_vehicule', [
            'intervention_id' => $this->interventionId,
            'vehicule_id' => $vehicule->id,
        ]);
    }
}
