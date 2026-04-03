<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Phase;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionPhaseTest extends TestCase
{
    use DatabaseTransactions;

    protected $interventionId;

    protected function setUp(): void
    {
        parent::setUp();

        $data = Intervention::factory()->make()->toArray();
        $data["date_debut"] = "2019-01-01";
        $this->interventionId = $this->json('POST', '/api/v2/interventions', $data)->json('data.id');
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
                        'intervention_id',
                        'phase_type_id',
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
    public function testAddInterventionPhases()
    {
        $phases = [
            [
                'debut' => '2019-12-12 12:30',
                'phase_type_id' => 1,
                'intervention_id' => $this->interventionId,
            ]
        ];

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
        $res = Phase::where('intervention_id', $this->interventionId)->get()
            ->filter(fn($p) => $p->debut === null)
            ->map(function ($s) {
                $s->debut = '2019-12-12 13:00';
                return $s;
            });

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
        $phases = [
            [
                'debut' => '2019-12-12 13:45',
                'phase_type_id' => 1,
                'intervention_id' => $this->interventionId,
            ]
        ];

        $allPhases = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/phases', ['phases' => $phases])->json('data');
        $ids = array_column(array_filter($allPhases, fn($p) => $p['debut'] !== null), 'id');

        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/phases', ['phases' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
