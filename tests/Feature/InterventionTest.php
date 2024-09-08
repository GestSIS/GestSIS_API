<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Intervention;
use Exception;
use Tests\TestCase;

class InterventionTest extends TestCase
{

    protected $service;
    protected $interventionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make('App\Domaine\API\InterventionService');
    }

    /**
     * Test index intervention
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionIndexOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test show intervention
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionShowOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/393");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'localite_id', 'date_debut'
                ]
            ]);
    }

    /**
     * Test add intervention
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionOk()
    {
        $intervention = Intervention::factory()->make();
        $response = $this->json('POST', '/api/v2/interventions', $intervention->toArray());

        // dd($intervention);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test validate exercice
     *
     * @return void
     * @throws Exception
     */
    public function testValidateInterventionInvalid()
    {
        $intervention = Intervention::factory()->create();

        $response = $this->json('POST', "/api/v2/interventions/$intervention->id/valider");

        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => true
            ]);
    }

    /**
     * Test validate exercice
     *
     * @return void
     * @throws Exception
     */
    public function testValidateInterventionOk()
    {
        $intervention = Intervention::factory()->create();

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

        $this->service->addPresences($intervention->id, $sapeurs);

        $response = $this->json('POST', "/api/v2/interventions/$intervention->id/valider");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test edit intervention
     *
     * @return void
     * @throws Exception
     */
    public function testEditIntervention()
    {
        $intervention = Intervention::factory()->create();
        $interventionEdited = Intervention::factory()->make();

        $response = $this->json(
            'PUT',
            '/api/v2/interventions/' . $intervention->id,
            $interventionEdited->toArray()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
