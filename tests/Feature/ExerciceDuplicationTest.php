<?php

namespace Tests\Feature;

use App\Domaine\Business\ExerciceBusiness;
use App\Models\ExcuseType;
use App\Models\Exercice;
use App\Models\ExerciceComptable;
use App\Models\ExerciceSapeur;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExerciceDuplicationTest extends TestCase
{
    use DatabaseTransactions;

    private Exercice $exercice;

    protected function setUp(): void
    {
        parent::setUp();

        $exerciceComptable = ExerciceComptable::create([
            'annee' => 2031,
            'designation' => 'Exercice 2031',
            'debut' => '2031-01-01',
            'fin' => '2031-12-31',
            'boucle' => 0,
        ]);
        $this->exercice = Exercice::factory()->create([
            'exercice_comptable_id' => $exerciceComptable->id,
            'date' => '2031-03-10',
            'designation' => 'Roulage véhicules mars',
            'statut' => ExerciceBusiness::EXERCICE_STATUT_IMPUTE,
        ])->refresh();
    }

    private function addConvocation(int $sapeurId, array $attributes = []): void
    {
        ExerciceSapeur::create([
            'exercice_id' => $this->exercice->id,
            'sapeur_id' => $sapeurId,
            'convoque' => 1,
            'present' => 0,
            'remplace' => 0,
            ...$attributes,
        ]);
    }

    private function duplicate(array $data = [])
    {
        return $this->json('POST', "/api/v2/exercices/{$this->exercice->id}/dupliquer", [
            'date' => '2031-04-14',
            'designation' => 'Roulage véhicules avril',
            'garder_sapeurs' => true,
            ...$data,
        ]);
    }

    public function testDuplicateCopiesFieldsWithNewDateAndDesignation(): void
    {
        $response = $this->duplicate();

        $response->assertStatus(201);
        $copy = Exercice::findOrFail($response->json('data.id'));
        $this->assertNotSame($this->exercice->id, $copy->id);
        $this->assertSame('2031-04-14', $copy->date);
        $this->assertSame('Roulage véhicules avril', $copy->designation);
        $this->assertSame(ExerciceBusiness::EXERCICE_STATUT_VIDE, $copy->statut);
        foreach (['heure', 'lieu', 'localite_id', 'communications', 'duree', 'exercice_categorie_id', 'exercice_comptable_id'] as $field) {
            $this->assertEquals($this->exercice->$field, $copy->$field, $field);
        }
    }

    public function testDuplicateKeepsOnlyConvocations(): void
    {
        $excusedSapeur = Sapeur::factory()->create();
        $presentSapeur = Sapeur::factory()->create();
        $notConvokedSapeur = Sapeur::factory()->create();
        $this->addConvocation($excusedSapeur->id, [
            'excuse_type_id' => ExcuseType::first()?->id,
            'excuse_statut' => ExerciceBusiness::EXCUSE_STATUT_ACCEPTEE,
            'remarque' => 'Vacances',
        ]);
        $this->addConvocation($presentSapeur->id, ['present' => 1]);
        $this->addConvocation($notConvokedSapeur->id, ['convoque' => 0, 'present' => 1]);

        $response = $this->duplicate();

        $response->assertStatus(201);
        $rows = ExerciceSapeur::where('exercice_id', $response->json('data.id'))->orderBy('sapeur_id')->get();
        $this->assertSame([$excusedSapeur->id, $presentSapeur->id], $rows->pluck('sapeur_id')->all());
        foreach ($rows as $row) {
            $this->assertSame(1, $row->convoque);
            $this->assertSame(0, $row->present);
            $this->assertSame(0, $row->absent);
            $this->assertSame(0, $row->remplace);
            $this->assertNull($row->excuse_type_id);
            $this->assertSame(ExerciceBusiness::EXCUSE_STATUT_A_TRAITER, $row->excuse_statut);
            $this->assertSame('', $row->remarque);
        }
    }

    public function testDuplicateWithoutSapeurs(): void
    {
        $this->addConvocation(Sapeur::factory()->create()->id);

        $response = $this->duplicate(['garder_sapeurs' => false]);

        $response->assertStatus(201);
        $this->assertSame(0, ExerciceSapeur::where('exercice_id', $response->json('data.id'))->count());
    }

    public function testDuplicateSkipsInactiveSapeurs(): void
    {
        $activeSapeur = Sapeur::factory()->create();
        $inactiveSapeur = Sapeur::factory()->inactif()->create();
        $this->addConvocation($activeSapeur->id);
        $this->addConvocation($inactiveSapeur->id);

        $response = $this->duplicate();

        $response->assertStatus(201);
        $this->assertSame(
            [$activeSapeur->id],
            ExerciceSapeur::where('exercice_id', $response->json('data.id'))->pluck('sapeur_id')->all(),
        );
    }

    public function testDuplicateKeepsActiveCivils(): void
    {
        $civil = Sapeur::factory()->civil()->create();
        $ancienSapeurCivil = Sapeur::factory()->ancienSapeurCivil()->create();
        $this->addConvocation($civil->id);
        $this->addConvocation($ancienSapeurCivil->id);

        $response = $this->duplicate();

        $response->assertStatus(201);
        $this->assertEqualsCanonicalizing(
            [$civil->id, $ancienSapeurCivil->id],
            ExerciceSapeur::where('exercice_id', $response->json('data.id'))->pluck('sapeur_id')->all(),
        );
    }

    public function testDuplicateAcceptsBoundaryDates(): void
    {
        $this->duplicate(['date' => '2031-01-01'])->assertStatus(201);
        $this->duplicate(['date' => '2031-12-31'])->assertStatus(201);
    }

    public function testDuplicateRefusedOutsideExerciceComptable(): void
    {
        $this->addConvocation(Sapeur::factory()->create()->id);
        $exerciceCount = Exercice::count();
        $convocationCount = ExerciceSapeur::count();

        $this->duplicate(['date' => '2030-12-31'])->assertStatus(422);
        $this->duplicate(['date' => '2032-01-01'])->assertStatus(422);

        $this->assertSame($exerciceCount, Exercice::count());
        $this->assertSame($convocationCount, ExerciceSapeur::count());
    }

    public function testDuplicateRequiresDate(): void
    {
        $this->duplicate(['date' => null])->assertStatus(422);
    }

    public function testDuplicateReturns404WhenExerciceNotFound(): void
    {
        $this->json('POST', '/api/v2/exercices/999999/dupliquer', [
            'date' => '2031-04-14',
            'designation' => 'X',
            'garder_sapeurs' => false,
        ])->assertStatus(404);
    }
}
