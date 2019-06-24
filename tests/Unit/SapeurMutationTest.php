<?php

namespace Tests\Unit;

use App\Models\Sapeur;
use App\Business\SapeurBusiness;
use Exception;
use Carbon\Carbon;
use Tests\TestCase;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SapeurMutationTest extends TestCase
{
    /**
     * Test add mutation
     *
     * @return void
     * @throws Exception
     */
    public function testAddMutation()
    {
        $id = 2;
        $data = array(
            'incorporation' => Carbon::createMidnightDate(2000,1,18),
            'sortie' => null,
            'motif' => '',
            'localite_id' => 1
        );

        $sapeur = SapeurBusiness::get($id);
        $mutation_id = $sapeur->addMutation($data)->id;

        $mutation = Sapeur::find($id)->mutations()->where('mutations.id', $mutation_id)->first();

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
        $id = 2;
        $data = array(
            'incorporation' => Carbon::createMidnightDate(2000,1,18),
            'sortie' => null,
            'motif' => '',
            'localite_id' => 1
        );

        $sapeur = SapeurBusiness::get($id);
        $mutation_id = $sapeur->addMutation($data)->id;

        $data = array(
            'incorporation' => Carbon::createMidnightDate(2000,1,16),
            'sortie' => Carbon::createMidnightDate(2000,1,20),
            'motif' => '',
            'localite_id' => 2
        );
        $sapeur->updateMutation(array_merge($data, ['id' => $mutation_id]));

        $mutation = Sapeur::find($id)->mutations()->where('mutations.id', $mutation_id)->first();

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
        $id = 2;
        $data = array(
            'incorporation' => Carbon::createMidnightDate(2000,1,16),
            'sortie' => Carbon::createMidnightDate(2000,1,20),
            'motif' => '',
            'localite_id' => 2
        );

        $sapeur = SapeurBusiness::get($id);
        $mutation_id = $sapeur->addMutation($data)->id;

        $sapeur->removeMutation($mutation_id);
        $permis = Sapeur::find($id)->mutations()->where('mutations.id', $mutation_id)->first();

        $this->assertTrue($permis === null);
    }
}
