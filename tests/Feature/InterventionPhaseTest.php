<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Intervention;
use App\Domaine\API\InterventionService;
use Exception;
use Tests\TestCase;
use Carbon\Carbon;

class InterventionPhaseTest extends TestCase
{

    protected $interventionService;
    protected $interventionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->interventionService = $this->app->make(InterventionService::class);

        $data = Intervention::factory()->make()->toArray();
        $data["date_debut"] = "2019-01-01";

        $this->interventionId = $this->interventionService->createIntervention($data)->id;
    }

    /**
     * Test index interventions
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionIndexAppelsOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/393/phases");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'intervention_id', 'phase_type_id', 'id'
                    ]
                ]
            ]);
    }

    /**
     * Test add phase
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionPhases()
    {
        $phases = [[
            'debut' => '2019-12-12 12:30',
            'phase_type_id' => 1,
            'intervention_id' => $this->interventionId,
        ]];

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/phases', ['phases' => $phases]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test edit phase
     *
     * @return void
     * @throws Exception
     */
    public function testEditInterventionPhases()
    {
        $res = $this->interventionService->getInterventionPhases($this->interventionId);
        $res = array_map(
            function ($s) {
                $s->debut = '2019-12-12 13:00';
                return $s;
            },
            array_filter(
                $res,
                function ($p) {
                    return $p->debut === null;
                }
            )
        );

        $response = $this->json('PUT', '/api/v2/interventions/' . $this->interventionId . '/phases', ['phases' => $res]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove phase
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveInterventionPhases()
    {
        $phases = [[
            'debut' => '2019-12-12 13:45',
            'phase_type_id' => 1,
            'intervention_id' => $this->interventionId,
        ]];

        $ids = array_map(
            function ($s) {
                return $s->id;
            },
            array_filter(
                $this->interventionService->addPhases($this->interventionId, $phases),
                function ($phase) {
                    return $phase->debut !== null;
                }
            )
        );

        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/phases', ['phases' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
