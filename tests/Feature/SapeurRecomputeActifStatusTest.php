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
        $sapeur = $this->createSapeur(['statut_sapeur' => SapeurBusiness::STATUT_INACTIF]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->subYear()->toDateString(),
            'sortie' => null,
            'motif' => 'Incorporation',
        ]);

        SapeurBusiness::recomputeSapeurStatuts();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'statut_sapeur' => SapeurBusiness::STATUT_ACTIF]);
    }

    public function testSapeurAvecMutationTermineeDevientInactif(): void
    {
        $sapeur = $this->createSapeur(['statut_sapeur' => SapeurBusiness::STATUT_ACTIF]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->subYears(2)->toDateString(),
            'sortie' => now()->subMonth()->toDateString(),
            'motif' => 'Démission',
        ]);

        SapeurBusiness::recomputeSapeurStatuts();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'statut_sapeur' => SapeurBusiness::STATUT_INACTIF]);
    }

    public function testSapeurAvecIncorporationProcheDevientActif(): void
    {
        $sapeur = $this->createSapeur(['statut_sapeur' => SapeurBusiness::STATUT_INACTIF]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->addMonths(2)->toDateString(),
            'sortie' => now()->addYears(5)->toDateString(),
            'motif' => 'Incorporation',
        ]);

        SapeurBusiness::recomputeSapeurStatuts();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'statut_sapeur' => SapeurBusiness::STATUT_ACTIF]);
    }

    public function testSapeurAvecIncorporationTropLointaineResteInactif(): void
    {
        $sapeur = $this->createSapeur(['statut_sapeur' => SapeurBusiness::STATUT_ACTIF]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->addMonths(6)->toDateString(),
            'sortie' => now()->addYears(5)->toDateString(),
            'motif' => 'Incorporation',
        ]);

        SapeurBusiness::recomputeSapeurStatuts();

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id, 'statut_sapeur' => SapeurBusiness::STATUT_INACTIF]);
    }

    public function testSapeurSansMutationDevientInactif(): void
    {
        $sapeur = $this->createSapeur(['statut_sapeur' => SapeurBusiness::STATUT_ACTIF]);

        SapeurBusiness::recomputeSapeurStatuts();

        $this->assertDatabaseHas('sapeurs', [
            'id' => $sapeur->id,
            'statut_sapeur' => SapeurBusiness::STATUT_JAMAIS,
            'statut_civil' => SapeurBusiness::STATUT_JAMAIS,
        ]);
    }

    public function testCivilAvecMutationCivileEnCoursDevientActif(): void
    {
        $civil = $this->createSapeur([]);
        $civil->mutations()->create([
            'nature' => SapeurBusiness::NATURE_CIVIL,
            'localite_id' => $civil->localite_id,
            'incorporation' => now()->subYear()->toDateString(),
            'sortie' => null,
            'motif' => '',
        ]);

        SapeurBusiness::recomputeSapeurStatuts();

        $this->assertDatabaseHas('sapeurs', [
            'id' => $civil->id,
            'statut_sapeur' => SapeurBusiness::STATUT_JAMAIS,
            'statut_civil' => SapeurBusiness::STATUT_ACTIF,
        ]);
    }

    /**
     * L'anticipation de 3 mois ne vaut que pour les mutations sapeur
     */
    public function testCivilAvecDebutProcheResteInactif(): void
    {
        $civil = $this->createSapeur(['statut_civil' => SapeurBusiness::STATUT_ACTIF]);
        $civil->mutations()->create([
            'nature' => SapeurBusiness::NATURE_CIVIL,
            'localite_id' => $civil->localite_id,
            'incorporation' => now()->addMonths(2)->toDateString(),
            'sortie' => null,
            'motif' => '',
        ]);

        SapeurBusiness::recomputeSapeurStatuts();

        $this->assertDatabaseHas('sapeurs', [
            'id' => $civil->id,
            'statut_civil' => SapeurBusiness::STATUT_INACTIF,
        ]);
    }

    public function testAncienSapeurDevenuCivilResteActifEnTantQueCivil(): void
    {
        $sapeur = $this->createSapeur([]);
        $sapeur->mutations()->create([
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->subYears(10)->toDateString(),
            'sortie' => now()->subYear()->toDateString(),
            'motif' => 'Fin de service',
        ]);
        $sapeur->mutations()->create([
            'nature' => SapeurBusiness::NATURE_CIVIL,
            'localite_id' => $sapeur->localite_id,
            'incorporation' => now()->subYear()->addDay()->toDateString(),
            'sortie' => null,
            'motif' => '',
        ]);

        SapeurBusiness::recomputeSapeurStatuts();

        $this->assertDatabaseHas('sapeurs', [
            'id' => $sapeur->id,
            'statut_sapeur' => SapeurBusiness::STATUT_INACTIF,
            'statut_civil' => SapeurBusiness::STATUT_ACTIF,
        ]);
    }
}
