<?php

namespace Tests\Feature;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
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

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $this->sapeurId = $this->service->createSapeur($data)->id;
    }

    /**
     * Test add permis
     *
     * @return void
     * @throws Exception
     */
    public function testPermisIndexOk()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/1/permis");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'permis_type_id', 'sapeur_id', 'date'
                    ]
                ]
            ]);
    }

    /**
     * Test add permis
     *
     * @return void
     * @throws Exception
     */
    public function testAddPermisOk()
    {
        $permis_type = 9;

        $response = $this->json(
            'POST',
            "/api/v2/sapeurs/$this->sapeurId/permis",
            ['permis_type_id' => $permis_type, 'date' => '1958-01-01']
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'permis_type_id', 'sapeur_id', 'date'
                ]
            ]);

        $permis = $response->getData()->data;

        $this->assertTrue($permis->permis_type_id === $permis_type);
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

        $response = $this->json(
            'POST',
            "/api/v2/sapeurs/$this->sapeurId/permis",
            ['permis_type_id' => $permis_type, 'date' => '1958-01-01']
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
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
        $date = "1999-11-21";

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/$this->sapeurId/permis/$permis->id",
            ['id' => $permis->id, 'date' => $date]
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'permis_type_id', 'sapeur_id', 'date'
                ]
            ]);

        $permis = $response->getData()->data;

        $this->assertTrue(Carbon::parse($date)->diffInDays($permis->date) === 0.0);
    }


    /**
     * Test edit permis
     *
     * @return void
     * @throws Exception
     */
    public function testEditPermisInvalid()
    {
        $permis_type = 3;
        $date = Carbon::createMidnightDate(1958, 1, 1);

        $permis = $this->service->addPermis($this->sapeurId, ['permis_type_id' => $permis_type, 'date' => $date]);
        $date = "1999-11-21";

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/$this->sapeurId/permis/0",
            ['id' => $permis->id, 'date' => $date]
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
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

        $permisId = $this->service->addPermis($this->sapeurId, ['permis_type_id' => $permis_type, 'date' => $date])->id;

        $response = $this->json('DELETE', "/api/v2/sapeurs/$this->sapeurId/permis/$permisId");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);

        $permis = $this->service->getSapeurPermisById($this->sapeurId);
        array_filter($permis, function ($p) use ($permisId) {
            return $p->id == $permisId;
        });

        $this->assertTrue(count($permis) === 0);
    }
}
