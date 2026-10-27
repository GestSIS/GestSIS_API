<?php

namespace Tests\Feature;

use App\Domaine\Business\ImputationBusiness;
use App\Models\Ecriture;
use App\Models\Exercice;
use App\Models\ExerciceComptable;
use App\Models\Intervention;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ComptabiliteStatistiqueTest extends TestCase
{
    use DatabaseTransactions;

    public function testModuleFicheDeTravailSepareParLien()
    {
        $exerciceComptableId = ExerciceComptable::create([
            'annee' => 2093, 'designation' => 'ec statistiques', 'debut' => '2093-01-01', 'fin' => '2093-12-31', 'boucle' => false,
        ])->id;
        $sapeurId = Sapeur::factory()->create()->id;
        $exercice = Exercice::factory()->create(['exercice_comptable_id' => $exerciceComptableId]);
        $intervention = Intervention::factory()->create(['exercice_comptable_id' => $exerciceComptableId]);

        $ecriture = fn(float $total, array $lien) => Ecriture::create([
            'designation' => 'test',
            'total' => $total,
            'tarif' => $total,
            'quantite' => 1,
            'type_unite_id' => 1,
            'type' => 1,
            'sapeur_id' => $sapeurId,
            'compte_id' => 1,
            'ecriture_categorie_id' => 1,
            'exercice_comptable_id' => $exerciceComptableId,
            'module' => ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL,
            ...$lien,
        ]);
        $ecriture(10, []);
        $ecriture(20, ['exercice_id' => $exercice->id]);
        $ecriture(30, ['intervention_id' => $intervention->id]);
        $ecriture(5, ['intervention_id' => $intervention->id]);

        $lignes = collect($this->json('GET', "/api/v2/statistiques/{$exerciceComptableId}/module-comptable")->json('data'))
            ->where('module', ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL)
            ->keyBy(fn($l) => $l['lien'] ?? 'aucun');

        $this->assertCount(3, $lignes);
        $this->assertEquals(10, $lignes['aucun']['total']);
        $this->assertEquals(20, $lignes['exercice']['total']);
        $this->assertEquals(35, $lignes['intervention']['total']);
        $this->assertEquals(2, $lignes['intervention']['nb']);
    }
}
