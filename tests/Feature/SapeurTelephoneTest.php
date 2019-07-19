<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Sapeur;
use App\Domaine\API\SapeurService;
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
    public function testAddTelephone()
    {
        $data = array(
            'numero' => '032 546 54 12',
            'telephone_type_id' => 1,
            'rta' => 0,
            'priorite' => 1
        );

        $telephone_id = $this->service->addTelephone($this->sapeurId, $data)->id;

        $telephone = Sapeur::find($this->sapeurId)->telephones()->where('sapeur_telephone.id', $telephone_id)->first();

        foreach ($data as $key => $value) {
            $this->assertTrue($data[$key] === $telephone[$key]);
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

        $telephone_id = $this->service->addTelephone($this->sapeurId, $data)->id;

        $data = array(
            'numero' => '032 546 12 18',
            'telephone_type_id' => 2,
            'rta' => 0,
            'priorite' => 3
        );
        $this->service->updateTelephone($this->sapeurId, array_merge($data, ['id' => $telephone_id]));

        $telephone = Sapeur::find($this->sapeurId)->telephones()->where('sapeur_telephone.id', $telephone_id)->first();

        foreach ($data as $key => $value) {
            $this->assertTrue($data[$key] === $telephone[$key]);
        }
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

        $telephone_id = $this->service->addTelephone($this->sapeurId, $data)->id;

        $this->service->removeTelephone($this->sapeurId, $telephone_id);
        $permis = Sapeur::find($this->sapeurId)->telephones()->where('sapeur_telephone.id', $telephone_id)->first();

        $this->assertTrue($permis === null);
    }
}
