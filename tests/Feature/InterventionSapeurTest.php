<?php

namespace Tests\Feature;

use App\Models\Intervention;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionSapeurTest extends TestCase
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
    public function testInterventionIndexSapeursOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/393/sapeurs");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'intervention_id',
                        'sapeur_id',
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
    public function testAddInterventionPresences()
    {
        $sapeurs = [
            [
                'sapeur_id' => 1,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
            [
                'sapeur_id' => 2,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
            [
                'sapeur_id' => 3,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
        ];

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/sapeurs', ['sapeurs' => $sapeurs]);

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
    public function testEditInterventionPresences()
    {
        $sapeurs = [
            [
                'sapeur_id' => 5,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
            [
                'sapeur_id' => 6,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
            [
                'sapeur_id' => 7,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
        ];

        $res = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/sapeurs', ['sapeurs' => $sapeurs])
            ->json('data.sapeurs');
        $res = array_map(function ($s) {
            $s['debut'] = substr($s['debut'], 0, 16);
            $s['fin'] = substr($s['fin'], 0, 16);
            return $s;
        }, $res);

        $response = $this->json('PUT', '/api/v2/interventions/' . $this->interventionId . '/sapeurs', ['sapeurs' => $res]);

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
    public function testRemoveInterventionPresences()
    {
        $sapeurs = [
            [
                'sapeur_id' => 7,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
            [
                'sapeur_id' => 8,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
            [
                'sapeur_id' => 9,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
        ];

        $ids = array_column(
            $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/sapeurs', ['sapeurs' => $sapeurs])->json('data.sapeurs'),
            'id'
        );
        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/sapeurs', ['sapeurs' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }


    /**
     * Test add quittance
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionQuittance()
    {
        $quittances = [1, 2, 3, 5];

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/quittances', ['quittances' => $quittances]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove quittance
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveInterventionQuittance()
    {
        $quittances = [1, 5];

        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/quittances/', ["quittances" => $quittances]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
