<?php

namespace Tests\Unit;

use App\Infrastructure\Models\Sapeur;
use App\Domaine\API\SapeurService;
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
     * Test add mutation
     *
     * @return void
     * @throws Exception
     */
    public function testAddMutation()
    {
        $data = array(
            'incorporation' => Carbon::createMidnightDate(2000, 1, 18),
            'sortie' => null,
            'motif' => '',
            'localite_id' => 1
        );

        $mutation_id = $this->service->addMutation($this->sapeurId, $data)->id;

        $mutation = Sapeur::find($this->sapeurId)->mutations()->where('mutations.id', $mutation_id)->first();

        $this->assertTrue($data['incorporation']->diffInDays($mutation->incorporation) === 0);
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
            'incorporation' => Carbon::createMidnightDate(2000, 1, 18),
            'sortie' => null,
            'motif' => '',
            'localite_id' => 1
        );

        $mutation_id = $this->service->addMutation($this->sapeurId, $data)->id;

        $data = array(
            'incorporation' => Carbon::createMidnightDate(2000, 1, 16),
            'sortie' => Carbon::createMidnightDate(2000, 1, 20),
            'motif' => '',
            'localite_id' => 2
        );
        $this->service->updateMutation($this->sapeurId, array_merge($data, ['id' => $mutation_id]));

        $mutation = Sapeur::find($this->sapeurId)->mutations()->where('mutations.id', $mutation_id)->first();

        $this->assertTrue($data['incorporation']->diffInDays($mutation->incorporation) === 0);
        $this->assertTrue($data['sortie']->diffInDays($mutation->sortie) === 0);
        $this->assertTrue($data['motif'] === $mutation->motif);
        $this->assertTrue($data['localite_id'] === $mutation->localite_id);
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

        $this->service->removeMutation($this->sapeurId, $mutation_id);
        $mutation = Sapeur::find($this->sapeurId)->mutations()->where('mutations.id', $mutation_id)->first();

        $this->assertTrue($mutation === null);
    }
}
