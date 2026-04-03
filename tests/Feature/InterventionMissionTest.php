<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Mission;
use App\Infrastructure\Models\Intervention;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionMissionTest extends TestCase
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
     * Test index interventions
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionsIndexMissionsOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/393/missions");

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
     * Test add presence
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionMissions()
    {
        $missions = Mission::factory()->count(3)->make(['intervention_id' => $this->interventionId]);

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/missions', ['missions' => $missions]);

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
    public function testEditInterventionMissions()
    {
        $missions = Mission::factory()->count(3)->make(['intervention_id' => $this->interventionId])->toArray();

        $res = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/missions', ['missions' => $missions])
            ->json('data');
        $res = array_map(function ($s) {
            $s['debut'] = substr($s['debut'], 0, 16);
            $s['fin'] = substr($s['fin'], 0, 16);
            return $s;
        }, $res);

        $response = $this->json('PUT', '/api/v2/interventions/' . $this->interventionId . '/missions', ['missions' => $res]);

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
    public function testRemoveInterventionMissions()
    {
        $missions = Mission::factory()->count(3)->make(['intervention_id' => $this->interventionId])->toArray();

        $ids = array_column(
            $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/missions', ['missions' => $missions])->json('data'),
            'id'
        );
        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/missions', ['missions' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
