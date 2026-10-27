<?php

namespace Tests\Feature;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\TravauxBusiness;
use App\Domaine\Business\TravauxParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\Intervention;
use App\Models\InterventionSapeur;
use App\Models\Sapeur;
use App\Models\Travail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TravailCascadeTest extends TestCase
{
    use DatabaseTransactions;

    protected int $sapeurId;
    protected int $typeId;
    protected Exercice $exercice;
    protected Intervention $intervention;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sapeurId = Sapeur::factory()->create()->id;
        $this->typeId = TravauxParamBusiness::ajouterType([
            'designation' => 'Rédition',
            'actif' => true,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
            'lien_exercice' => true,
            'lien_intervention' => true,
            'fonctions' => [['type' => 1, 'tarif' => 30.00, 'compte_id' => 1, 'fonction_id' => null]],
        ])->id;
        $this->exercice = Exercice::factory()->create(['statut' => ExerciceBusiness::EXERCICE_STATUT_SAISI]);
        $this->intervention = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_SAISI]);
    }

    /**
     * @param array<string, mixed> $lien
     */
    private function fiche(array $lien, int $statut): Travail
    {
        return Travail::create([
            'designation' => 'Rédition',
            'date' => '2026-10-13',
            'date_demande' => '2026-10-13',
            'quantite' => 2,
            'justification' => 'à vider',
            'statut' => $statut,
            'sapeur_id' => $this->sapeurId,
            'auteur_id' => $this->sapeurId,
            'travail_type_id' => $this->typeId,
            'exercice_comptable_id' => $this->exercice->exercice_comptable_id,
            ...$lien,
        ]);
    }

    public function testValiderExerciceRefuseSiFicheEnAttente()
    {
        $saisie = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_SAISI);

        try {
            ExerciceBusiness::validateExerciceById($this->exercice->id);
            $this->fail('La validation aurait dû être refusée');
        } catch (ArrayException $e) {
            $this->assertSame('Certaines fiches de travail liées doivent être validées au préalable', $e->getMessage());
        }

        $this->assertSame(ExerciceBusiness::EXERCICE_STATUT_SAISI, $this->exercice->fresh()->statut);
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $saisie->fresh()->statut);
    }

    public function testValiderExerciceAvecFichesTraiteesNeLesModifiePas()
    {
        $validee = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        $invalidee = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_INVALIDE);

        ExerciceBusiness::validateExerciceById($this->exercice->id);

        $this->assertSame(ExerciceBusiness::EXERCICE_STATUT_VALIDE, $this->exercice->fresh()->statut);
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $validee->fresh()->statut);
        $this->assertSame('à vider', $validee->fresh()->justification);
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_INVALIDE, $invalidee->fresh()->statut);
    }

    public function testDevaliderExerciceNeModifiePasLesFiches()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);
        $validee = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);

        ExerciceBusiness::devaliderExerciceById($this->exercice->id);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $validee->fresh()->statut);
    }

    public function testValiderInterventionRefuseSiFicheEnAttente()
    {
        $this->fiche(['intervention_id' => $this->intervention->id], TravauxBusiness::TRAVAIL_STATUT_SAISI);

        $this->expectException(ArrayException::class);
        InterventionBusiness::validerInterventionById($this->intervention->id);
    }

    public function testValiderInterventionAvecFicheValidee()
    {
        $validee = $this->fiche(['intervention_id' => $this->intervention->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);

        InterventionBusiness::validerInterventionById($this->intervention->id);

        $this->assertSame(InterventionBusiness::INTERVENTION_STATUT_VALIDE, $this->intervention->fresh()->statut);
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $validee->fresh()->statut);
    }

    public function testSupprimerExerciceSupprimeFichesLiees()
    {
        $fiche = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_SAISI);

        ExerciceBusiness::deleteExerciceById($this->exercice->id);

        $this->assertNull(Travail::find($fiche->id));
    }

    public function testSupprimerInterventionSupprimeFichesLiees()
    {
        $fiche = $this->fiche(['intervention_id' => $this->intervention->id], TravauxBusiness::TRAVAIL_STATUT_SAISI);

        InterventionBusiness::deleteInterventionById($this->intervention->id);

        $this->assertNull(Travail::find($fiche->id));
    }

    public function testAnnulerExerciceValideNeModifiePasLesFiches()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);
        $validee = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);

        ExerciceBusiness::cancelExerciceById($this->exercice->id);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $validee->fresh()->statut);
    }

    public function testPresenceIncompleteSurExerciceValideNeModifiePasLesFiches()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);
        $validee = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        ExerciceSapeur::create([
            'exercice_id' => $this->exercice->id,
            'sapeur_id' => $this->sapeurId,
            'convoque' => true,
            'present' => false,
            'absent' => false,
            'remplace' => false,
            'amende' => false,
        ]);

        $statut = ExerciceBusiness::updateStatut($this->exercice->id);

        $this->assertSame(ExerciceBusiness::EXERCICE_STATUT_VIDE, $statut);
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $validee->fresh()->statut);
    }

    public function testSupprimerDernierePresenceInterventionValideeNeModifiePasLesFiches()
    {
        $this->intervention->update(['statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE]);
        $validee = $this->fiche(['intervention_id' => $this->intervention->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        $presence = new InterventionSapeur(['piquet' => false, 'debut' => '2026-10-12 18:45:00', 'fin' => '2026-10-12 22:00:00']);
        $presence->intervention_id = $this->intervention->id;
        $presence->sapeur_id = $this->sapeurId;
        $presence->save();

        InterventionBusiness::removePresences($this->intervention->id, [$presence->id]);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $validee->fresh()->statut);
    }

    public function testFicheDUnAutreEvenementIntouchee()
    {
        $autreExercice = Exercice::factory()->create(['statut' => ExerciceBusiness::EXERCICE_STATUT_SAISI]);
        $autre = $this->fiche(['exercice_id' => $autreExercice->id], TravauxBusiness::TRAVAIL_STATUT_SAISI);
        $libre = $this->fiche([], TravauxBusiness::TRAVAIL_STATUT_SAISI);

        ExerciceBusiness::validateExerciceById($this->exercice->id);
        ExerciceBusiness::deleteExerciceById($this->exercice->id);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $autre->fresh()->statut);
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $libre->fresh()->statut);
    }
}
