<?php

namespace Tests\Feature;

use App\Models\ExerciceComptable;
use App\Models\Intervention;
use App\Models\InterventionSapeur;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurInterventionTest extends TestCase
{
    use DatabaseTransactions;

    private function creerExerciceComptable(int $annee): ExerciceComptable
    {
        return ExerciceComptable::create([
            'annee' => $annee,
            'designation' => (string) $annee,
            'debut' => "$annee-01-01",
            'fin' => "$annee-12-31",
            'boucle' => false,
        ]);
    }

    public function testIndexReturnsInterventionsForSapeurAndExerciceComptable(): void
    {
        $sapeur = Sapeur::factory()->create();
        $exerciceComptable = $this->creerExerciceComptable(2990);
        $intervention = Intervention::factory()->create(['exercice_comptable_id' => $exerciceComptable->id]);
        $interventionSapeur = new InterventionSapeur();
        $interventionSapeur->intervention_id = $intervention->id;
        $interventionSapeur->sapeur_id = $sapeur->id;
        $interventionSapeur->debut = '2024-01-01 12:00';
        $interventionSapeur->fin = '2024-01-01 13:00';
        $interventionSapeur->piquet = false;
        $interventionSapeur->save();

        $response = $this->json(
            'GET',
            "/api/v2/sapeurs/{$sapeur->id}/interventions/{$exerciceComptable->id}",
            [],
            ['Sis-Key' => 1],
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'intervention_id',
                    'sapeur_id',
                    'intervention' => [
                        'date_debut',
                        'heure_debut',
                        'lieu',
                        'objet',
                    ],
                ],
            ],
        ]);
        $ids = collect($response->json('data'))->pluck('intervention_id');
        $this->assertTrue($ids->contains($intervention->id));
    }

    public function testIndexExcludesInterventionsFromOtherExerciceComptable(): void
    {
        $sapeur = Sapeur::factory()->create();
        $exerciceComptable = $this->creerExerciceComptable(2991);
        $autreExerciceComptable = $this->creerExerciceComptable(2992);
        $intervention = Intervention::factory()->create(['exercice_comptable_id' => $autreExerciceComptable->id]);
        $interventionSapeur = new InterventionSapeur();
        $interventionSapeur->intervention_id = $intervention->id;
        $interventionSapeur->sapeur_id = $sapeur->id;
        $interventionSapeur->debut = '2024-01-01 12:00';
        $interventionSapeur->fin = '2024-01-01 13:00';
        $interventionSapeur->piquet = false;
        $interventionSapeur->save();

        $response = $this->json(
            'GET',
            "/api/v2/sapeurs/{$sapeur->id}/interventions/{$exerciceComptable->id}",
            [],
            ['Sis-Key' => 1],
        );

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function testIndexExcludesInterventionsFromOtherSapeur(): void
    {
        $sapeur = Sapeur::factory()->create();
        $autreSapeur = Sapeur::factory()->create();
        $exerciceComptable = $this->creerExerciceComptable(2993);
        $intervention = Intervention::factory()->create(['exercice_comptable_id' => $exerciceComptable->id]);
        $interventionSapeur = new InterventionSapeur();
        $interventionSapeur->intervention_id = $intervention->id;
        $interventionSapeur->sapeur_id = $autreSapeur->id;
        $interventionSapeur->debut = '2024-01-01 12:00';
        $interventionSapeur->fin = '2024-01-01 13:00';
        $interventionSapeur->piquet = false;
        $interventionSapeur->save();

        $response = $this->json(
            'GET',
            "/api/v2/sapeurs/{$sapeur->id}/interventions/{$exerciceComptable->id}",
            [],
            ['Sis-Key' => 1],
        );

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }
}
