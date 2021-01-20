<?php

namespace Test\Feature;

use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\InterventionMateriel;
use App\Domaine\API\InterventionService;
use Exception;
use Tests\TestCase;

class InterventionMaterielTest extends TestCase
{

    protected $interventionService;
    protected $interventionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->interventionService = $this->app->make(InterventionService::class);

        $data = Intervention::factory()->make()->toArray();

        $this->interventionId = $this->interventionService->createIntervention($data)->id;
    }

    /**
     * Test index interventions
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionIndexMaterielOK()
    {
        $response = $this->json('GET', "/api/v2/interventions/393/materiels");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'intervention_id', 'materiel_id'
                    ]
                ]
            ]);
    }

    /**
     * Test add presence
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionMateriels()
    {
        $materiels = InterventionMateriel::factory()->count(1)->make();

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => $materiels]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test edit presence
     *
     * @return void
     * @throws Exception
     */
    public function testEditInterventionMateriels()
    {
        $data = Intervention::factory()->make()->toArray();

        $this->interventionId = $this->interventionService->createIntervention($data)->id;

        $materiels = InterventionMateriel::factory()->count(1)->make(['intervention_id' => $this->interventionId])->toArray();

        $res = $this->interventionService->addMateriels($this->interventionId, $materiels);

        $response = $this->json('PUT', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => $res]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove presence
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveInterventionMateriels()
    {
        $data = Intervention::factory()->make()->toArray();

        $this->interventionId = $this->interventionService->createIntervention($data)->id;

        $materiels = InterventionMateriel::factory()->count(1)->make(['intervention_id' => $this->interventionId])->toArray();

        $ids = array_map(function ($s) {
            return $s->id;
        }, $this->interventionService->addMateriels($this->interventionId, $materiels));
        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
