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
    public function testPermisIndexOK()
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
    public function testCivilitesIndexOK()
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
    public function testGradeIndexOK()
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
    public function testFonctionIndexOK()
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
    public function testCoursIndexOK()
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
    public function testTelephonesTypesIndexOK()
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
     * Test index grupe sapeur
     *
     * @return void
     * @throws Exception
     */
    public function testGroupeSapeurIndexOK()
    {
        $response = $this->json('GET', "/api/v2/groupes-sapeurs/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'no',
                        'designation',
                        'sapeurs' => [
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
    public function testLocaliteIndexOK()
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
