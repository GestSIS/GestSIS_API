<?php

namespace Tests\Unit;

use App\Infrastructure\Models\Intervention;
use Exception;
use Tests\TestCase;

class InterventionSapeurTest extends TestCase
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
     * Test add presence
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionPresences()
    {
        $sapeurs = array(
            array(
                'sapeur_id' => 1,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 2,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 3,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
        );

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
        $sapeurs = array(
            array(
                'sapeur_id' => 5,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 6,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 7,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
        );

        $res = $this->interventionService->addPresences($this->interventionId, $sapeurs)['sapeurs'];
        $res = array_map(function ($s) {
            $s->debut = substr($s->debut, 0, 16);
            $s->fin = substr($s->fin, 0, 16);
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
        $sapeurs = array(
            array(
                'sapeur_id' => 7,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 8,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 9,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
        );

        $ids = array_map(function ($s) {
            return $s->id;
        }, $this->interventionService->addPresences($this->interventionId, $sapeurs)['sapeurs']);
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

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/quittances', array('quittances' => $quittances));

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

        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/quittances/', array("quittances" => $quittances));

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
