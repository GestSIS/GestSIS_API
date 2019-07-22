<?php

namespace Tests\Feature;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;


class SapeurTelephoneTest extends TestCase
{
    protected $service;
    protected $sapeurId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(SapeurService::class);

        $data = factory(Sapeur::class)->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $this->sapeurId = $this->service->createSapeur($data)->id;
    }

    /**
     * Test add telephone
     *
     * @return void
     * @throws Exception
     */
    public function testTelephoneIndexOK()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/1/telephones");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'telephone_type_id', 'sapeur_id', 'numero'
                    ]
                ]
            ]);
    }

    /**
     * Test add telephone
     *
     * @return void
     * @throws Exception
     */
    public function testAddTelephone()
    {
        $data = array(
            'numero' => '032 546 54 12',
            'telephone_type_id' => 1,
            'rta' => 0,
            'priorite' => 1
        );

        $response = $this->json('POST', "/api/v2/sapeurs/$this->sapeurId/telephones", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'telephone_type_id', 'sapeur_id', 'rta', 'priorite', 'numero'
                ]
            ]);

        $telephone = $response->getData()->data;

        foreach ($data as $key => $value) {
            $this->assertTrue($data[$key] === get_object_vars($telephone)[$key]);
        }
    }

    /**
     * Test edit telephone
     *
     * @return void
     * @throws Exception
     */
    public function testEditTelephone()
    {
        $data = array(
            'numero' => '032 546 54 15',
            'telephone_type_id' => 1,
            'rta' => 0,
            'priorite' => 1
        );

        $telephoneId = $this->service->addTelephone($this->sapeurId, $data)->id;

        $data = array(
            'numero' => '032 546 12 18',
            'telephone_type_id' => 2,
            'rta' => 0,
            'priorite' => 3
        );

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/$this->sapeurId/telephones/$telephoneId",
            array_merge($data, ['id' => $telephoneId])
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'telephone_type_id', 'sapeur_id', 'rta', 'priorite', 'numero'
                ]
            ]);

        $telephone = $response->getData()->data;

        foreach ($data as $key => $value) {
            $this->assertTrue($data[$key] === get_object_vars($telephone)[$key]);
        }
    }

    /**
     * Test edit telephone
     *
     * @return void
     * @throws Exception
     */
    public function testEditTelephoneInvalid()
    {
        $data = array(
            'numero' => '032 546 54 15',
            'telephone_type_id' => 1,
            'rta' => 0,
            'priorite' => 1
        );

        $telephoneId = $this->service->addTelephone($this->sapeurId, $data)->id;

        $data = array(
            'numero' => '032 546 12 18',
            'telephone_type_id' => 2,
            'rta' => 0,
            'priorite' => 3
        );

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/$this->sapeurId/telephones/0",
            array_merge($data, ['id' => $telephoneId])
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test remove telephone
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveTelephone()
    {
        $data = array(
            'numero' => '032 546 54 79',
            'telephone_type_id' => 1,
            'rta' => 0,
            'priorite' => 1
        );

        $telephoneId = $this->service->addTelephone($this->sapeurId, $data)->id;

        $response = $this->json('DELETE', "/api/v2/sapeurs/$this->sapeurId/telephones/$telephoneId");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);

        $telephones = $this->service->getSapeurTelephonesById($this->sapeurId);
        array_filter($telephones, function ($p) use ($telephoneId) {
            return $p->id == $telephoneId;
        });

        $this->assertTrue(count($telephones) === 0);
    }
}
