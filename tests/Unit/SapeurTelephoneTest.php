<?php

namespace Tests\Feature;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Exception;
use Tests\TestCase;


class SapeurTelephoneTest extends TestCase
{
    /**
     * Test add telephone
     *
     * @return void
     */
    public function testAddTelephone()
    {
        $id = 2;
        $data = array(
            'numero' => '032 546 54 76',
            'telephone_type_id' => 1,
            'rta' => 0,
            'priorite' => 1
        );

        $sapeur = SapeurBusiness::get($id);
        $telephone_id = $sapeur->addTelephone($data)->id;

        $telephone = Sapeur::find($id)->telephones()->where('sapeur_telephone.id', $telephone_id)->first();

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
        $id = 2;
        $data = array(
            'numero' => '032 546 54 76',
            'telephone_type_id' => 1,
            'rta' => 0,
            'priorite' => 1
        );

        $sapeur = SapeurBusiness::get($id);
        $telephone_id = $sapeur->addTelephone($data)->id;

        $data = array(
            'numero' => '032 546 12 43',
            'telephone_type_id' => 2,
            'rta' => 0,
            'priorite' => 3
        );
        $sapeur->updateTelephone(array_merge($data, ['telephone_id' => $telephone_id]));

        $telephone = Sapeur::find($id)->telephones()->where('sapeur_telephone.id', $telephone_id)->first();

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
        $id = 2;
        $data = array(
            'numero' => '032 546 54 76',
            'telephone_type_id' => 1,
            'rta' => 0,
            'priorite' => 1
        );

        $sapeur = SapeurBusiness::get($id);
        $telephone_id = $sapeur->addTelephone($data)->id;

        $sapeur->removeTelephone($telephone_id);
        $permis = Sapeur::find($id)->telephones()->where('sapeur_telephone.id', $telephone_id)->first();

        $this->assertTrue($permis === null);
    }
}
