<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BaseDataInterventionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test index missions types
     *
     * @return void
     * @throws Exception
     */
    public function testMissionTypesIndexOk()
    {
        $response = $this->json('GET', "/api/v2/mission-types/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'titre'
                    ]
                ]
            ]);
    }

    /**
     * Test index stat federal
     *
     * @return void
     * @throws Exception
     */
    public function testStatFederalIndexOk()
    {
        $response = $this->json('GET', "/api/v2/stat-federal/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test index intervention types
     *
     * @return void
     * @throws Exception
     */
    public function testTypeInterventionIndexOk()
    {
        $response = $this->json('GET', "/api/v2/type-intervention/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test index intervention traitement
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionTraitementIndexOk()
    {
        $response = $this->json('GET', "/api/v2/intervention-traitement/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test index phase types
     *
     * @return void
     * @throws Exception
     */
    public function testPhaseTypesIndexOk()
    {
        $response = $this->json('GET', "/api/v2/phase-types/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test index vehicules
     *
     * @return void
     * @throws Exception
     */
    public function testVehiculesIndexOk()
    {
        $response = $this->json('GET', "/api/v2/vehicules/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test index vehicules
     *
     * @return void
     * @throws Exception
     */
    public function testMaterielsIndexOk()
    {
        $response = $this->json('GET', "/api/v2/materiels/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test index telephones
     *
     * @return void
     * @throws Exception
     */
    public function testTelephonesIndexOk()
    {
        $response = $this->json('GET', "/api/v2/telephones/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'numero'
                    ]
                ]
            ]);
    }
}
