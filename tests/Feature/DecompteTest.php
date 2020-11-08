<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeompteTest extends TestCase
{
    /**
     * création d'un décompte.
     *
     * @return void
     */
    public function testDecompteCreation()
    {
        $response = $this->json('GET', "api/v2/decompte/create");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'exercice_comptable_id',
                    'deduction'
                ]
            ]);
    }

    /**
     * get all décomptes.
     *
     * @return void
     */
    public function testDecompteAll()
    {
        $response = $this->json('GET', "api/v2/decompte/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation',
                        'exercice_comptable_id',
                        'deduction'
                    ]
                ]
            ]);
    }

    /**
     * get all décomptes.
     *
     * @return void
     */
    public function testDecompteAnneeComptable()
    {
        $response = $this->json('GET', "api/v2/decompte/exerciceComptable/3");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation',
                        'exercice_comptable_id',
                        'deduction'
                    ]
                ]
            ]);
    }

    /**
     * get un décompte.
     *
     * @return void
     */
    public function testDecompte()
    {
        $response = $this->json('GET', "api/v2/decompte/1");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'exercice_comptable_id',
                    'deduction'
                ]
            ]);
    }
}
