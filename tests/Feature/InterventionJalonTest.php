<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Jalon;
use App\Models\Sapeur;
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
     * Test add jalon with a sapeur_id (sapeur of this SIS)
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionJalonWithSapeurId()
    {
        $sapeur = Sapeur::factory()->create();
        $jalon = Jalon::factory()->make(['sapeur_id' => $sapeur->id, 'sapeur' => null])->toArray();

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/jalons', ['jalons' => [$jalon]]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
        $this->assertEquals($sapeur->id, $response->json('data.0.sapeur_id'));
    }

    /**
     * Test add jalon with a free-text sapeur (sapeur external au SIS)
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionJalonWithSapeurExterne()
    {
        $jalon = Jalon::factory()->make(['sapeur_id' => null, 'sapeur' => 'Jean Dupont'])->toArray();

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/jalons', ['jalons' => [$jalon]]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
        $this->assertEquals('Jean Dupont', $response->json('data.0.sapeur'));
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
