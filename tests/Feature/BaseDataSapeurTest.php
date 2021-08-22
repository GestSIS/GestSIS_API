<?php

namespace Test\Feature;

use Exception;
use Tests\TestCase;

class BaseDataSapeurTest extends TestCase
{

    /**
     * Test index permis
     *
     * @return void
     * @throws Exception
     */
    public function testPermisIndexOk()
    {
        $response = $this->json('GET', "/api/v2/permis/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'type'
                    ]
                ]
            ]);
    }

    /**
     * Test index civilites
     *
     * @return void
     * @throws Exception
     */
    public function testCivilitesIndexOk()
    {
        $response = $this->json('GET', "/api/v2/civilites/");

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
     * Test index grades
     *
     * @return void
     * @throws Exception
     */
    public function testGradeIndexOk()
    {
        $response = $this->json('GET', "/api/v2/grades/");

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
     * Test index grades
     *
     * @return void
     * @throws Exception
     */
    public function testFonctionIndexOk()
    {
        $response = $this->json('GET', "/api/v2/fonctions/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'nom'
                    ]
                ]
            ]);
    }

    /**
     * Test index cours
     *
     * @return void
     * @throws Exception
     */
    public function testCoursIndexOk()
    {
        $response = $this->json('GET', "/api/v2/cours/");

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
     * Test index telephones types
     *
     * @return void
     * @throws Exception
     */
    public function testTelephonesTypesIndexOk()
    {
        $response = $this->json('GET', "/api/v2/telephone-types/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'type'
                    ]
                ]
            ]);
    }

    /**
     * Test index groupe sapeur
     *
     * @return void
     * @throws Exception
     */
    public function testGroupeSapeurIndexOk()
    {
        $response = $this->json('GET', "/api/v2/groupes/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'no',
                        'designation',
                        'sapeur_ids' => [
                            '*' => [
                                'id',
                                'sapeur_id'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test index grupe sapeur
     *
     * @return void
     * @throws Exception
     */
    public function testLocaliteIndexOk()
    {
        $response = $this->json('GET', "/api/v2/localites/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation', 'npa'
                    ]
                ]
            ]);
    }
}
