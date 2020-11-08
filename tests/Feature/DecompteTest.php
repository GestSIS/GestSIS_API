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
        $data = array();
        $data['designation'] = "test";
        $data['taux_avs'] = "0.04";
        $data['taux_ac'] = "0.02";
        $data['deduction'] = "1";
        $data['exerciceComptableId'] = "4";
        $response = $this->json('POST', "api/v2/decompte/create", $data);

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
