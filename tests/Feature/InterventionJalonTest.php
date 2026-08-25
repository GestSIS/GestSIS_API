<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Jalon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionJalonTest extends TestCase
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
    public function testInterventionIndexJalonsOk()
    {
        $response = $this->json('GET', '/api/v2/interventions/' . $this->interventionId . '/jalons');

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
     * Test add jalon
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionJalons()
    {
        $jalons = Jalon::factory()->count(3)->make();

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/jalons', ['jalons' => $jalons]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test edit jalon
     *
     * @return void
     * @throws Exception
     */
    public function testEditInterventionJalons()
    {
        $jalons = Jalon::factory()->count(3)->make()->toArray();

        $res = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/jalons', ['jalons' => $jalons])
            ->json('data');
        $res = array_map(function ($j) {
            $j['date_time'] = substr($j['date_time'], 0, 16);
            return $j;
        }, $res);

        $response = $this->json('PUT', '/api/v2/interventions/' . $this->interventionId . '/jalons', ['jalons' => $res]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove jalon
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveInterventionJalons()
    {
        $jalons = Jalon::factory()->count(3)->make()->toArray();

        $ids = array_column(
            $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/jalons', ['jalons' => $jalons])->json('data'),
            'id'
        );
        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/jalons', ['jalons' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
