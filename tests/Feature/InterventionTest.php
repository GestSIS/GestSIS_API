<?php

namespace Tests\Unit;

use App\Infrastructure\Models\Intervention;
use Exception;
use Tests\TestCase;

class InterventionTest extends TestCase
{
    /**
     * Test add intervention
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionOK()
    {
        $intervention = factory(Intervention::class)->make();

        $response = $this->json('POST', '/api/v2/interventions', $intervention->toArray());

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
        $intervention = factory(Intervention::class)->create();
        $interventionEdited = factory(Intervention::class)->make();

        $response = $this->json(
            'PUT',
            '/api/v2/interventions/' . $intervention->id, $interventionEdited->toArray()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
