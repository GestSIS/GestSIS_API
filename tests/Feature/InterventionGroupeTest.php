<?php

namespace Test\Feature;

use App\Infrastructure\Models\Intervention;
use Exception;
use Tests\TestCase;

class InterventionGroupeTest extends TestCase
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
     * Test index interventions
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionIndexGroupesOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/393/groupes");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'intervention_id', 'groupe_id'
                    ]
                ]
            ]);
    }

    /**
     * Test add groupe
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionGroupe()
    {
        $groupes = [1, 2, 3, 5];

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/groupes', array('groupes' => $groupes));

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove groupe
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveInterventionGroupe()
    {
        $groupes = [1, 5];

        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/groupes/', array("groupes" => $groupes));

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
