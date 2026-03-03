<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\Appel;
use App\Domaine\API\InterventionService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionAppelTest extends TestCase
{
    use DatabaseTransactions;

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
    public function testInterventionIndexAppelsOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/393/appels");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'intervention_id',
                        'id'
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
    public function testAddInterventionAppels()
    {
        $appels = Appel::factory()->count(3)->make();

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/appels', ['appels' => $appels]);

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
    public function testEditInterventionAppels()
    {
        $appels = Appel::factory()->count(3)->make()->toArray();

        $res = $this->interventionService->addAppels($this->interventionId, $appels)
            ->map(function ($s) {
                $s->date = substr($s->date, 0, 16);
                return $s;
            })->toArray();

        $response = $this->json('PUT', '/api/v2/interventions/' . $this->interventionId . '/appels', ['appels' => $res]);

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
    public function testRemoveInterventionAppels()
    {
        $appels = Appel::factory()->count(3)->make()->toArray();

        $ids = $this->interventionService->addAppels($this->interventionId, $appels)
            ->map(fn($s) => $s->id)
            ->toArray();
        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/appels', ['appels' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
