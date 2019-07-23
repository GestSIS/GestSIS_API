<?php

namespace Tests\Unit;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
use Exception;
use Tests\TestCase;

class BaseDataInterventionTest extends TestCase
{
    /**
     * Test index missions types
     *
     * @return void
     * @throws Exception
     */
    public function testMissionTypesIndexOK()
    {
        $response = $this->json('GET', "/api/v2/mission-types/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'titre'
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
    public function testStatFederalIndexOK()
    {
        $response = $this->json('GET', "/api/v2/stat-federal/");

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
     * Test index intervention types
     *
     * @return void
     * @throws Exception
     */
    public function testTypeInterventionIndexOK()
    {
        $response = $this->json('GET', "/api/v2/type-intervention/");

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
     * Test index intervention traitement
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionTraitementIndexOK()
    {
        $response = $this->json('GET', "/api/v2/intervention-traitement/");

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
     * Test index phase types
     *
     * @return void
     * @throws Exception
     */
    public function testPhaseTypesIndexOK()
    {
        $response = $this->json('GET', "/api/v2/phase-types/");

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
     * Test index vehicules
     *
     * @return void
     * @throws Exception
     */
    public function testVehiculesIndexOK()
    {
        $response = $this->json('GET', "/api/v2/vehicules/");

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
     * Test index vehicules
     *
     * @return void
     * @throws Exception
     */
    public function testMaterielsIndexOK()
    {
        $response = $this->json('GET', "/api/v2/materiels/");

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
     * Test index telephones
     *
     * @return void
     * @throws Exception
     */
    public function testTelephonesIndexOK()
    {
        $response = $this->json('GET', "/api/v2/telephones/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'numero'
                    ]
                ]
            ]);
    }
}
