<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Sapeur;
use App\Domaine\API\SapeurService;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class SapeurPermisTest extends TestCase
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
     * Test add permis
     *
     * @return void
     * @throws Exception
     */
    public function testAddPermisOK()
    {
        $permis_type = 9;

        $response = $this->json('GET', '/api/v2/sapeurs');

        $this->service->addPermis($this->sapeurId, ['permis_type_id' => $permis_type, 'date' => Carbon::parse('1958-01-01')]);

        $permis = Sapeur::find($this->sapeurId)->permis()->where('permis_type_id', $permis_type)->first();
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
        $permis_type = 4;
        $date = Carbon::createFromDate(1958, 1, 1);

        $this->service->addPermis($this->sapeurId, ['permis_type_id' => $permis_type, 'date' => $date]);
        try {
            $this->service->addPermis($this->sapeurId, ['permis_type_id' => $permis_type, 'date' => $date]);
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
        $permis_type = 2;
        $date = Carbon::createMidnightDate(1958, 1, 1);

        $permis = $this->service->addPermis($this->sapeurId, ['permis_type_id' => $permis_type, 'date' => $date]);
        $date = Carbon::createMidnightDate(1999, 11, 21);

        $this->service->updatePermis($this->sapeurId, ['id' => $permis->id, 'date' => $date]);
        $permis = Sapeur::find($this->sapeurId)->permis()->where('permis.id', $permis->id)->first();

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
        $permis_type = 7;
        $date = Carbon::createMidnightDate(1958, 1, 1);

        $permis = $this->service->addPermis($this->sapeurId, ['permis_type_id' => $permis_type, 'date' => $date]);
        $date = Carbon::createMidnightDate(1999, 11, 21);

        $this->service->removePermis($this->sapeurId, $permis->id);
        $permis = Sapeur::find($this->sapeurId)->permis()->where('permis.id', $permis->id)->first();

        $this->assertTrue($permis === null);
    }
}
