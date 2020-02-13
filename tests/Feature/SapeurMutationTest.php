<?php

namespace Tests\Unit;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class SapeurMutationTest extends TestCase
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
    public function testGradeIndexOK()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/1/mutations");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'incorporation', 'sapeur_id', 'sortie', 'motif'
                    ]
                ]
            ]);
    }

    /**
     * Test add mutation
     *
     * @return void
     * @throws Exception
     */
    public function testAddMutation()
    {
        $data = array(
            'incorporation' => "2000-01-18",
            'sortie' => "2000-01-29",
            'motif' => '',
            'localite_id' => 1
        );

        $response = $this->json('POST', "/api/v2/sapeurs/$this->sapeurId/mutations", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'incorporation', 'sapeur_id', 'sortie', 'motif'
                ]
            ]);

        $mutation = $response->getData()->data;

        $this->assertTrue(Carbon::parse($data['incorporation'])->diffInDays($mutation->incorporation) === 0);
        $this->assertTrue($data['sortie'] === $mutation->sortie);
        $this->assertTrue($data['motif'] === $mutation->motif);
        $this->assertTrue($data['localite_id'] === $mutation->localite_id);
    }

    /**
     * Test edit mutation
     *
     * @return void
     * @throws Exception
     */
    public function testEditMutation()
    {
        $data = array(
            'incorporation' => Carbon::createMidnightDate(2002, 1, 18),
            'sortie' => Carbon::createMidnightDate(2002, 1, 29),
            'motif' => '',
            'localite_id' => 1
        );

        $mutation_id = $this->service->addMutation($this->sapeurId, $data)->id;

        $data = array(
            'incorporation' => "2005-01-16",
            'sortie' => "2005-01-20",
            'motif' => '',
            'localite_id' => 2
        );

        $response = $this->json('PUT', "/api/v2/sapeurs/$this->sapeurId/mutations/$mutation_id",
            array_merge($data, ['id' => $mutation_id])
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'incorporation', 'sapeur_id', 'sortie', 'motif'
                ]
            ]);

        $mutation = $response->getData()->data;

        $this->assertTrue(Carbon::parse($data['incorporation'])->diffInDays($mutation->incorporation) === 0);
        $this->assertTrue(Carbon::parse($data['sortie'])->diffInDays($mutation->sortie) === 0);
        $this->assertTrue($data['motif'] === $mutation->motif);
        $this->assertTrue($data['localite_id'] === $mutation->localite_id);
    }

    /**
     * Test edit mutation
     *
     * @return void
     * @throws Exception
     */
    public function testEditMutationInvalid()
    {
        $data = array(
            'incorporation' => Carbon::createMidnightDate(2003, 1, 18),
            'sortie' => Carbon::createMidnightDate(2005, 1, 18),
            'motif' => '',
            'localite_id' => 1
        );

        $mutation_id = $this->service->addMutation($this->sapeurId, $data)->id;

        $data = array(
            'incorporation' => "2000-01-16",
            'sortie' => Null,
            'motif' => '',
            'localite_id' => 2
        );

        $response = $this->json('PUT', "/api/v2/sapeurs/$this->sapeurId/mutations/0",
            array_merge($data, ['id' => $mutation_id])
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test remove mutation
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveMutation()
    {
        $data = array(
            'incorporation' => Carbon::createMidnightDate(2000, 1, 16),
            'sortie' => Carbon::createMidnightDate(2000, 1, 20),
            'motif' => '',
            'localite_id' => 2
        );

        $mutation_id = $this->service->addMutation($this->sapeurId, $data)->id;

        $response = $this->json('DELETE', "/api/v2/sapeurs/$this->sapeurId/mutations/$mutation_id");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);

        $mutations = $this->service->getSapeurMutationsById($this->sapeurId);
        $mutations = array_filter($mutations, function ($m) use ($mutation_id) {
            return $m->id == $mutation_id;
        });

        $this->assertTrue(count($mutations) === 0);
    }
}
