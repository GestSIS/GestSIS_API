<?php

namespace Tests\Feature;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\TravauxBusiness;
use App\Domaine\Business\TravauxParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Exercice;
use App\Models\ExerciceComptable;
use App\Models\Intervention;
use App\Models\Sapeur;
use App\Models\Travail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TravailLienTest extends TestCase
{
    use DatabaseTransactions;

    protected int $sapeurId;
    protected int $autreSapeurId;
    protected Exercice $exercice;
    protected Intervention $intervention;
    protected int $typeLibreId;
    protected int $typeExerciceId;
    protected int $typeObligatoireId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sapeurId = Sapeur::factory()->create()->id;
        $this->autreSapeurId = Sapeur::factory()->create()->id;
        $this->exercice = Exercice::factory()->create(['statut' => ExerciceBusiness::EXERCICE_STATUT_SAISI]);
        $this->intervention = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_SAISI]);

        $this->typeLibreId = $this->creerType([])->id;
        $this->typeExerciceId = $this->creerType(['lien_exercice' => true])->id;
        $this->typeObligatoireId = $this->creerType([
            'lien_exercice' => true,
            'lien_intervention' => true,
            'lien_obligatoire' => true,
        ])->id;
    }

    /**
     * @param array<string, bool> $liens
     */
    private function creerType(array $liens)
    {
        return TravauxParamBusiness::ajouterType([
            'designation' => 'Type test',
            'actif' => true,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
            'fonctions' => [['type' => 1, 'tarif' => 30.00, 'compte_id' => 1, 'fonction_id' => null]],
            ...$liens,
        ]);
    }

    /**
     * @param array<string, mixed> $valeurs
     * @return array<string, mixed>
     */
    private function travail(array $valeurs): array
    {
        return [
            'designation' => 'Rédition',
            'date' => '2026-10-13',
            'quantite' => 2,
            'sapeur_id' => $this->sapeurId,
            'travail_type_id' => $this->typeObligatoireId,
            'exercice_comptable_id' => $this->exercice->exercice_comptable_id,
            ...$valeurs,
        ];
    }

    /**
     * @param array<string, mixed> $valeurs
     */
    private function ajouter(array $valeurs, bool $validationExercice = false, bool $validationIntervention = false): Travail
    {
        return TravauxBusiness::ajouter([$this->travail($valeurs)], $this->sapeurId, true, $validationExercice, $validationIntervention)[0];
    }

    public function testAjouterLieExerciceImposeExerciceComptable()
    {
        $autre = ExerciceComptable::create([
            'annee' => 2097,
            'designation' => 'autre exercice comptable',
            'debut' => '2097-01-01',
            'fin' => '2097-12-31',
            'boucle' => false,
        ]);

        $travail = $this->ajouter(['exercice_id' => $this->exercice->id, 'exercice_comptable_id' => $autre->id]);

        $this->assertSame($this->exercice->exercice_comptable_id, $travail->fresh()->exercice_comptable_id);
    }

    public function testAjouterRefuseDeuxLiens()
    {
        $this->expectException(ArrayException::class);
        $this->ajouter(['exercice_id' => $this->exercice->id, 'intervention_id' => $this->intervention->id]);
    }

    public function testAjouterRefuseLienNonAutorise()
    {
        $this->expectException(ArrayException::class);
        $this->ajouter(['travail_type_id' => $this->typeLibreId, 'exercice_id' => $this->exercice->id]);
    }

    public function testAjouterRefuseInterventionSiTypeExerciceSeulement()
    {
        $this->expectException(ArrayException::class);
        $this->ajouter(['travail_type_id' => $this->typeExerciceId, 'intervention_id' => $this->intervention->id]);
    }

    public function testAjouterRefuseSansLienSiObligatoire()
    {
        $this->expectException(ArrayException::class);
        $this->ajouter([]);
    }

    public function testAjouterSurEvenementValideSansDroitRefuse()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);

        $this->expectException(ArrayException::class);
        $this->ajouter(['exercice_id' => $this->exercice->id]);
    }

    public function testAjouterSurEvenementValideAvecDroitDeLAutreEvenementRefuse()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);

        $this->expectException(ArrayException::class);
        $this->ajouter(['exercice_id' => $this->exercice->id], false, true);
    }

    public function testAjouterSurEvenementValideAvecDroitCreeValidee()
    {
        $this->intervention->update(['statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE]);

        $travail = $this->ajouter(['intervention_id' => $this->intervention->id], false, true);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $travail->fresh()->statut);
    }

    public function testAjouterSurEvenementSaisiCreeSaisie()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id], true);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $travail->fresh()->statut);
    }

    public function testAjouterSurEvenementImputeRefuseMemeAvecDroit()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_IMPUTE]);

        $this->expectException(ArrayException::class);
        $this->ajouter(['exercice_id' => $this->exercice->id], true, true);
    }

    public function testAjouterSurExerciceAnnuleRefuse()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_ANNULE]);

        $this->expectException(ArrayException::class);
        $this->ajouter(['exercice_id' => $this->exercice->id], true);
    }

    public function testModifierDeplacementVersEvenementImputeRefuse()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->intervention->update(['statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE]);

        $this->expectException(ArrayException::class);
        TravauxBusiness::modifier($travail->id, $this->travail(['intervention_id' => $this->intervention->id]), $this->sapeurId, true, true);
    }

    public function testModifierDepuisEvenementValideSansDroitRefuse()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);

        $this->expectException(ArrayException::class);
        TravauxBusiness::modifier($travail->id, $this->travail(['intervention_id' => $this->intervention->id]), $this->sapeurId);
    }

    public function testModifierParValidateurSansEtreAuteur()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);

        $modifie = TravauxBusiness::modifier(
            $travail->id,
            $this->travail(['exercice_id' => $this->exercice->id, 'quantite' => 3]),
            $this->autreSapeurId,
            true
        );

        $this->assertEquals(3, $modifie->fresh()->quantite);
    }

    public function testModifierFicheLieeSansDroitParAutreQueAuteurRefuse()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);

        $this->expectException(ArrayException::class);
        TravauxBusiness::modifier($travail->id, $this->travail(['exercice_id' => $this->exercice->id]), $this->autreSapeurId);
    }

    public function testSupprimerSurEvenementValideSansDroitRefuse()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);

        $this->expectException(ArrayException::class);
        TravauxBusiness::supprimer($travail->id, $this->sapeurId);
    }

    public function testSupprimerSurEvenementValideAvecDroit()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);

        TravauxBusiness::supprimer($travail->id, $this->autreSapeurId, true);

        $this->assertNull(Travail::find($travail->id));
    }

    public function testFicheSansLienInchangee()
    {
        $travail = $this->ajouter(['travail_type_id' => $this->typeLibreId]);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $travail->fresh()->statut);
        $this->assertNull($travail->fresh()->exercice_id);

        $this->expectException(ArrayException::class);
        TravauxBusiness::modifier($travail->id, $this->travail(['travail_type_id' => $this->typeLibreId]), $this->autreSapeurId, true, true);
    }
}
