<?php

namespace Tests\Unit;

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

        $data = factory(Intervention::class)->make()->toArray();

        $this->interventionId = $this->interventionService->createIntervention($data)->id;
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
     * Test remove vehicule
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveInterventionVehicule()
    {
        $vehicules = [1, 5];

        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/vehicules/', array("vehicules" => $vehicules));

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
