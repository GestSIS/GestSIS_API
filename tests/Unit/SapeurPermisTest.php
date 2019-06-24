<?php

namespace Tests\Feature;

use App\Models\Sapeur;
use App\Business\SapeurBusiness;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class SapeurPermisTest extends TestCase
{
    /**
     * Test add permis
     *
     * @return void
     * @throws Exception
     */
    public function testAddPermisOK()
    {
        $id = 2;
        $permis_type = 9;

        //Remove potential pre-existant permis
        Sapeur::find($id)->permis()->where('permis_type_id', $permis_type)->delete();

        $sapeur = SapeurBusiness::get($id);
        $sapeur->addPermis(['permis_type_id' => $permis_type, 'date' => Carbon::parse('1958-01-01')]);

        $permis = Sapeur::find($id)->permis()->where('permis_type_id', $permis_type)->first();
        $this->assertTrue($permis !== null);
    }

    /**
     * Test duplicated permis add
     *
     * @return void
     * @throws Exception
     */
    public function testAddPermisDuplicated()
    {
        $id = 2;
        $permis_type = 9;
        $date = Carbon::createFromDate(1958, 1, 1);

        //Remove potential pre-existant permis
        Sapeur::find($id)->permis()->where('permis_type_id', $permis_type)->delete();

        $sapeur = SapeurBusiness::get($id);
        $sapeur->addPermis(['permis_type_id' => $permis_type, 'date' => $date]);
        try {
            $sapeur->addPermis(['permis_type_id' => $permis_type, 'date' => $date]);
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Test edit permis
     *
     * @return void
     * @throws Exception
     */
    public function testEditPermis()
    {
        $id = 2;
        $permis_type = 9;
        $date = Carbon::createMidnightDate(1958, 1, 1);

        //Remove potential pre-existant permis
        Sapeur::find($id)->permis()->where('permis_type_id', $permis_type)->delete();

        $sapeur = SapeurBusiness::get($id);
        $permis = $sapeur->addPermis(['permis_type_id' => $permis_type, 'date' => $date]);
        $date = Carbon::createMidnightDate(1999, 11, 21);

        $sapeur->updatePermis(['permis_id' => $permis->id, 'date' => $date]);
        $permis = Sapeur::find($id)->permis()->where('permis.id', $permis->id)->first();

        $this->assertTrue($date->diffInDays($permis->date) === 0);
    }

    /**
     * Test remove permis
     *
     * @return void
     * @throws Exception
     */
    public function testRemovePermis()
    {
        $id = 2;
        $permis_type = 9;
        $date = Carbon::createMidnightDate(1958, 1, 1);

        //Remove potential pre-existant permis
        Sapeur::find($id)->permis()->where('permis_type_id', $permis_type)->delete();

        $sapeur = SapeurBusiness::get($id);
        $permis = $sapeur->addPermis(['permis_type_id' => $permis_type, 'date' => $date]);
        $date = Carbon::createMidnightDate(1999, 11, 21);

        $sapeur->removePermis($permis->id);
        $permis = Sapeur::find($id)->permis()->where('permis.id', $permis->id)->first();

        $this->assertTrue($permis === null);
    }
}
