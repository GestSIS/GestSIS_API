<?php

namespace Tests\Feature;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurRecomputeActifStatusTest extends TestCase
{
    use DatabaseTransactions;

    private function createSapeur(array $attributes): Sapeur
    {
        return Sapeur::create(array_merge(
            Sapeur::factory()->make()->toArray(),
            $attributes,
        ));
    }

    public function testSapeurAvecMutationEnCoursDevientActif(): void
    {
        $sapeur = $this->createSapeur(['actif' => 0]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->subYear()->toDateString(),
            'sortie' => null,
            'motif' => 'Incorporation',
        ]);

        SapeurBusiness::recomputeSapeurActifStatus();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'actif' => 1]);
    }

    public function testSapeurAvecMutationTermineeDevientInactif(): void
    {
        $sapeur = $this->createSapeur(['actif' => 1]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->subYears(2)->toDateString(),
            'sortie' => now()->subMonth()->toDateString(),
            'motif' => 'Démission',
        ]);

        SapeurBusiness::recomputeSapeurActifStatus();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'actif' => 0]);
    }

    public function testSapeurAvecIncorporationProcheDevientActif(): void
    {
        $sapeur = $this->createSapeur(['actif' => 0]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->addMonths(2)->toDateString(),
            'sortie' => now()->addYears(5)->toDateString(),
            'motif' => 'Incorporation',
        ]);

        SapeurBusiness::recomputeSapeurActifStatus();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'actif' => 1]);
    }

    public function testSapeurAvecIncorporationTropLointaineResteInactif(): void
    {
        $sapeur = $this->createSapeur(['actif' => 0]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->addMonths(6)->toDateString(),
            'sortie' => now()->addYears(5)->toDateString(),
            'motif' => 'Incorporation',
        ]);

        SapeurBusiness::recomputeSapeurActifStatus();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'actif' => 0]);
    }

    public function testSapeurSansMutationDevientInactif(): void
    {
        $sapeur = $this->createSapeur(['actif' => 1]);

        SapeurBusiness::recomputeSapeurActifStatus();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'actif' => 0]);
    }

    public function testCivilAvecMutationEnCoursNestPasModifie(): void
    {
        $civil = $this->createSapeur(['actif' => 0, 'type' => SapeurBusiness::TYPE_CIVIL]);
        $civil->mutations()->create([
            'localite_id' => $civil->localite_id,
            'incorporation' => now()->subYear()->toDateString(),
            'sortie' => null,
            'motif' => 'Incorporation',
        ]);

        SapeurBusiness::recomputeSapeurActifStatus();

        $this->assertDatabaseHas('sapeurs', ['id' => $civil->id, 'actif' => 0]);
    }
}
