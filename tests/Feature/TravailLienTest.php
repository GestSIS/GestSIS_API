<?php

namespace Tests\Feature;

use App\Application\Http\Controllers\MesTravauxController;
use App\Application\Http\Controllers\TravailController;
use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\TravauxBusiness;
use App\Domaine\Business\TravauxParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Exercice;
use App\Models\ExerciceComptable;
use App\Models\ExerciceSapeur;
use App\Models\Intervention;
use App\Models\InterventionSapeur;
use App\Models\Sapeur;
use App\Models\Travail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
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

    public function testReviewFicheLieeAvecDroitFicheAccepte()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);

        TravauxBusiness::review($travail->id, true, '', 2, true, false, false);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $travail->fresh()->statut);
    }

    public function testReviewFicheLieeSansAucunDroitRefuse()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);

        $this->expectException(ArrayException::class);
        TravauxBusiness::review($travail->id, true, '', 2, false, false, true);
    }

    public function testReviewSurEvenementValideAvecDroitFicheAccepte()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        TravauxBusiness::review($travail->id, false, 'Doublon', 2, true, false, false);
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);

        TravauxBusiness::review($travail->id, true, '', 2, true, false, false);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $travail->fresh()->statut);
    }

    public function testReviewFicheLieeAvecDroitEvenementAccepte()
    {
        $travail = $this->ajouter(['intervention_id' => $this->intervention->id]);

        TravauxBusiness::review($travail->id, false, 'Doublon', 2, false, false, true);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_INVALIDE, $travail->fresh()->statut);
        $this->assertSame('Doublon', $travail->fresh()->justification);
    }

    public function testReviewFicheLibreAvecDroitFicheAccepte()
    {
        $travail = $this->ajouter(['travail_type_id' => $this->typeLibreId]);

        TravauxBusiness::review($travail->id, true, '', 2, true, false, false);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $travail->fresh()->statut);
    }

    public function testReviewFicheLibreSansDroitFicheRefuse()
    {
        $travail = $this->ajouter(['travail_type_id' => $this->typeLibreId]);

        $this->expectException(ArrayException::class);
        TravauxBusiness::review($travail->id, true, '', 2, false, true, true);
    }

    public function testCancelReviewFicheLieeAvecDroitEvenement()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        TravauxBusiness::review($travail->id, true, '', 2, false, true, false);

        TravauxBusiness::cancelReview($travail->id, false, true, false);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $travail->fresh()->statut);
    }

    public function testCancelReviewFicheLieeAvecDroitFicheAccepte()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        TravauxBusiness::review($travail->id, true, '', 2, false, true, false);

        TravauxBusiness::cancelReview($travail->id, true, false, false);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $travail->fresh()->statut);
    }

    public function testCancelReviewFicheLieeSansAucunDroitRefuse()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        TravauxBusiness::review($travail->id, true, '', 2, false, true, false);

        $this->expectException(ArrayException::class);
        TravauxBusiness::cancelReview($travail->id, false, false, true);
    }

    public function testModifierDeplacementVersEvenementValideValideLaFiche()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->intervention->update(['statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE]);

        TravauxBusiness::modifier($travail->id, $this->travail(['intervention_id' => $this->intervention->id]), $this->sapeurId, false, true);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $travail->fresh()->statut);
    }

    public function testModifierDeplacementVersEvenementSaisiRemetEnSaisie()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id], true);

        TravauxBusiness::modifier($travail->id, $this->travail(['intervention_id' => $this->intervention->id]), $this->sapeurId, true);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $travail->fresh()->statut);
    }

    public function testModifierDetacherFicheValideeRemetEnSaisie()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);
        $travail = $this->ajouter(['travail_type_id' => $this->typeExerciceId, 'exercice_id' => $this->exercice->id], true);

        TravauxBusiness::modifier($travail->id, $this->travail(['travail_type_id' => $this->typeExerciceId]), $this->sapeurId, true);

        $this->assertNull($travail->fresh()->exercice_id);
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $travail->fresh()->statut);
    }

    public function testModifierSansChangerLienGardeLeStatut()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id], true);

        TravauxBusiness::modifier($travail->id, $this->travail(['exercice_id' => $this->exercice->id, 'quantite' => 4]), $this->sapeurId, true);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $travail->fresh()->statut);
    }

    public function testReviewFicheLieeEvenementImputeRefuse()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_IMPUTE]);

        $this->expectException(ArrayException::class);
        TravauxBusiness::review($travail->id, true, '', 2, false, true, false);
    }

    public function testReviewFicheLieeExerciceAnnuleRefuse()
    {
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_ANNULE]);

        $this->expectException(ArrayException::class);
        TravauxBusiness::review($travail->id, true, '', 2, false, true, false);
    }

    public function testCancelReviewSurEvenementValideRefuse()
    {
        $this->exercice->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);
        $travail = $this->ajouter(['exercice_id' => $this->exercice->id], true);

        $this->expectException(ArrayException::class);
        TravauxBusiness::cancelReview($travail->id, false, true, false);
    }

    public function testListeFichesExercice()
    {
        $liee = $this->ajouter(['exercice_id' => $this->exercice->id]);
        $this->ajouter(['intervention_id' => $this->intervention->id]);
        $this->ajouter(['travail_type_id' => $this->typeLibreId]);

        $response = $this->json('GET', "/api/v2/exercices/{$this->exercice->id}/travaux");

        $response->assertStatus(200);
        $this->assertSame([$liee->id], $response->collect('data')->pluck('id')->all());
        $this->assertSame('Type test', $response->json('data.0.travail_type.designation'));
    }

    public function testListeFichesIntervention()
    {
        $this->ajouter(['exercice_id' => $this->exercice->id]);
        $liee = $this->ajouter(['intervention_id' => $this->intervention->id]);

        $response = $this->json('GET', "/api/v2/interventions/{$this->intervention->id}/travaux");

        $response->assertStatus(200);
        $this->assertSame([$liee->id], $response->collect('data')->pluck('id')->all());
        $this->assertSame('Type test', $response->json('data.0.travail_type.designation'));
    }

    public function testIndexChargeLInterventionLiee()
    {
        $liee = $this->ajouter(['intervention_id' => $this->intervention->id]);
        $request = Request::create('/api/v2/travaux');
        $request->attributes->set('permissions', ['fiche_travail.lecture']);

        $response = app(TravailController::class)->index($request, $this->intervention->exercice_comptable_id);

        $travail = collect($response->getData(true)['data'])->firstWhere('id', $liee->id);
        $this->assertSame($this->intervention->objet, $travail['intervention']['objet']);
        $this->assertArrayHasKey('date_debut', $travail['intervention']);
        $this->assertSame(InterventionBusiness::INTERVENTION_STATUT_SAISI, $travail['intervention']['statut']);
    }

    public function testMesTravauxChargeLInterventionLiee()
    {
        $liee = $this->ajouter(['intervention_id' => $this->intervention->id]);
        $request = Request::create('/api/v2/mes-travaux');
        $request->attributes->set('sapeurId', $this->sapeurId);

        $response = app(MesTravauxController::class)->index($request, $this->intervention->exercice_comptable_id);

        $travail = collect($response->getData(true)['data'])->firstWhere('id', $liee->id);
        $this->assertSame($this->intervention->objet, $travail['intervention']['objet']);
    }

    /**
     * @param array<int, string> $permissions
     * @return array{interventions: array<int, array<string, mixed>>, exercices: array<int, array<string, mixed>>}
     */
    private function evenements(array $permissions, ?int $sapeurId, int $exerciceComptableId): array
    {
        $request = Request::create('/api/v2/travaux/evenements');
        $request->attributes->set('permissions', $permissions);
        $request->attributes->set('sapeurId', $sapeurId);

        return app(TravailController::class)->evenements($request, $exerciceComptableId)->getData(true)['data'];
    }

    public function testEvenementsAvecDroitsLectureRenvoieTousLesEvenementsOuverts()
    {
        $exerciceComptableId = ExerciceComptable::create([
            'annee' => 2095, 'designation' => 'ec evenements', 'debut' => '2095-01-01', 'fin' => '2095-12-31', 'boucle' => false,
        ])->id;
        $saisi = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_SAISI, 'exercice_comptable_id' => $exerciceComptableId]);
        $validee = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE, 'exercice_comptable_id' => $exerciceComptableId]);
        Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE, 'exercice_comptable_id' => $exerciceComptableId]);
        $exercice = Exercice::factory()->create(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE, 'exercice_comptable_id' => $exerciceComptableId]);
        Exercice::factory()->create(['statut' => ExerciceBusiness::EXERCICE_STATUT_ANNULE, 'exercice_comptable_id' => $exerciceComptableId]);
        Exercice::factory()->create(['statut' => ExerciceBusiness::EXERCICE_STATUT_IMPUTE, 'exercice_comptable_id' => $exerciceComptableId]);

        $data = $this->evenements(['intervention.lecture', 'exercice.lecture'], null, $exerciceComptableId);

        $this->assertEqualsCanonicalizing([$saisi->id, $validee->id], array_column($data['interventions'], 'id'));
        $this->assertSame([$exercice->id], array_column($data['exercices'], 'id'));
        $this->assertSame(InterventionBusiness::INTERVENTION_STATUT_VALIDE, collect($data['interventions'])->firstWhere('id', $validee->id)['statut']);
        $this->assertArrayHasKey('objet', $data['interventions'][0]);
        $this->assertArrayHasKey('designation', $data['exercices'][0]);
    }

    public function testEvenementsSansDroitLectureRenvoieSeulementCeuxDuSapeur()
    {
        $exerciceComptableId = ExerciceComptable::create([
            'annee' => 2094, 'designation' => 'ec evenements sapeur', 'debut' => '2094-01-01', 'fin' => '2094-12-31', 'boucle' => false,
        ])->id;
        $presente = Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_SAISI, 'exercice_comptable_id' => $exerciceComptableId]);
        Intervention::factory()->create(['statut' => InterventionBusiness::INTERVENTION_STATUT_SAISI, 'exercice_comptable_id' => $exerciceComptableId]);
        $presence = new InterventionSapeur(['piquet' => false, 'debut' => '2094-03-01 18:00:00', 'fin' => '2094-03-01 20:00:00']);
        $presence->intervention_id = $presente->id;
        $presence->sapeur_id = $this->sapeurId;
        $presence->save();
        $convoque = Exercice::factory()->create(['statut' => ExerciceBusiness::EXERCICE_STATUT_SAISI, 'exercice_comptable_id' => $exerciceComptableId]);
        Exercice::factory()->create(['statut' => ExerciceBusiness::EXERCICE_STATUT_SAISI, 'exercice_comptable_id' => $exerciceComptableId]);
        ExerciceSapeur::create(['exercice_id' => $convoque->id, 'sapeur_id' => $this->sapeurId, 'convoque' => true, 'present' => false, 'absent' => false, 'remplace' => false, 'amende' => false]);

        $data = $this->evenements(['fiche_travail.saisie_perso'], $this->sapeurId, $exerciceComptableId);

        $this->assertSame([$presente->id], array_column($data['interventions'], 'id'));
        $this->assertSame([$convoque->id], array_column($data['exercices'], 'id'));
    }

    public function testEvenementsSansDroitNiSapeurRenvoieRien()
    {
        $data = $this->evenements(['fiche_travail.saisie_commune'], null, $this->intervention->exercice_comptable_id);

        $this->assertSame([], $data['interventions']);
        $this->assertSame([], $data['exercices']);
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
