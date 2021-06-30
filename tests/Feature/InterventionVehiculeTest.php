<?php

namespace Test\Feature;

use App\Infrastructure\Models\Intervention;
use Exception;
use Tests\TestCase;

class InterventionVehiculeTest extends TestCase
{

    protected $interventionService;
    protected $interventionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->interventionService = $this->app->make('App\Domaine\API\InterventionService');

        $data = Intervention::factory()->make()->toArray();

        $this->interventionId = $this->interventionService->createIntervention($data)->id;
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
                        'intervention_id', 'id', 'vehicule_id'
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

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', array('vehicules' => $vehicules));

        $response
            ->assertStatus(200)
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

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', array('vehicules' => $vehicules));
        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', array('vehicules' => $vehicules));

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ])
            ->assertJsonCount(
                count($vehicules), 'data'
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

        $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/vehicules', array('vehicules' => $vehiculesAdd));

        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/vehicules/', array("vehicules" => $vehiculesRemove));

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
