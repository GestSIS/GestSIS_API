<?php

namespace Tests\Feature;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\TravauxBusiness;
use App\Domaine\Business\TravauxParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Intervention;
use App\Models\Sapeur;
use App\Models\Travail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionDevalidationTest extends TestCase
{
    use DatabaseTransactions;

    public function testDevaliderInterventionValideeRepasseEnSaisie()
    {
        $intervention = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE]);

        $response = $this->json('POST', "/api/v2/interventions/{$intervention->id}/devalider");

        $response->assertStatus(200);
        $this->assertSame(InterventionBusiness::INTERVENTION_STATUT_SAISI, $response->json('data'));
        $this->assertSame(InterventionBusiness::INTERVENTION_STATUT_SAISI, $intervention->fresh()->statut);
    }

    public function testDevaliderInterventionSaisieRefuse()
    {
        $intervention = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_SAISI]);

        $this->expectException(ArrayException::class);
        InterventionBusiness::devaliderInterventionById($intervention->id);
    }

    public function testDevaliderInterventionImputeeRefuse()
    {
        $intervention = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE]);

        try {
            InterventionBusiness::devaliderInterventionById($intervention->id);
            $this->fail('La dévalidation aurait dû être refusée');
        } catch (ArrayException $e) {
            $this->assertSame("Impossible d'annuler la validation de l'intervention.", $e->getMessage());
        }

        $this->assertSame(InterventionBusiness::INTERVENTION_STATUT_IMPUTE, $intervention->fresh()->statut);
    }

    public function testDevaliderInterventionNeModifiePasLesFichesLiees()
    {
        $intervention = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE]);
        $sapeurId = Sapeur::factory()->create()->id;
        $type = TravauxParamBusiness::ajouterType([
            'designation' => 'Rédition',
            'actif' => true,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
            'lien_intervention' => true,
            'fonctions' => [['type' => 1, 'tarif' => 30.00, 'compte_id' => 1, 'fonction_id' => null]],
        ]);
        $fiche = Travail::create([
            'designation' => 'Rédition',
            'date' => '2026-10-13',
            'date_demande' => '2026-10-13',
            'quantite' => 2,
            'justification' => '',
            'statut' => TravauxBusiness::TRAVAIL_STATUT_VALIDE,
            'sapeur_id' => $sapeurId,
            'auteur_id' => $sapeurId,
            'travail_type_id' => $type->id,
            'exercice_comptable_id' => $intervention->exercice_comptable_id,
            'intervention_id' => $intervention->id,
        ]);

        InterventionBusiness::devaliderInterventionById($intervention->id);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $fiche->fresh()->statut);
    }
}
