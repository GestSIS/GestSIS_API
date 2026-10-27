<?php

namespace Tests\Feature;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\TravauxBusiness;
use App\Domaine\Business\TravauxParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Ecriture;
use App\Models\Exercice;
use App\Models\ExerciceComptable;
use App\Models\IndemniteInterventionType;
use App\Models\Intervention;
use App\Models\Sapeur;
use App\Models\Travail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TravailImputationTest extends TestCase
{
    use DatabaseTransactions;

    protected int $sapeurId;
    protected int $typeId;
    protected int $exerciceComptableId;
    protected Exercice $exercice;
    protected Intervention $intervention;
    protected int $indemniteInterventionTypeId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sapeurId = Sapeur::factory()->create()->id;
        $this->exerciceComptableId = ExerciceComptable::create([
            'annee' => 2096,
            'designation' => 'exercice comptable ouvert',
            'debut' => '2096-01-01',
            'fin' => '2096-12-31',
            'boucle' => false,
        ])->id;

        // Deux lignes cumulées : une écriture par ligne
        $this->typeId = TravauxParamBusiness::ajouterType([
            'designation' => 'Rédition',
            'actif' => true,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
            'lien_exercice' => true,
            'lien_intervention' => true,
            'fonctions' => [
                ['type' => 1, 'tarif' => 30.00, 'compte_id' => 1, 'fonction_id' => null],
                ['type' => 2, 'tarif' => 5.00, 'compte_id' => 2, 'fonction_id' => null],
            ],
        ])->id;

        $this->exercice = Exercice::factory()->create([
            'statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE,
            'exercice_comptable_id' => $this->exerciceComptableId,
        ]);
        $this->intervention = Intervention::factory()->create([
            'statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE,
            'exercice_comptable_id' => $this->exerciceComptableId,
        ]);

        $this->indemniteInterventionTypeId = IndemniteInterventionType::create([
            'designation' => 'Type Simple',
            'tarif' => 30,
            'tarif_min' => 40,
            'tarif_min_pour' => 1,
            'taux_weekend' => null,
            'taux_nuit' => null,
            'debut' => null,
            'fin' => null,
            'compte_id' => 4,
            'phase_id' => 1,
            'type_unite_id' => 2,
            'ecriture_categorie_id' => 4,
            'par_fonction' => false,
            'type' => 1,
            'tarif_pro_rata' => false,
            'tarif_min_pro_rata' => false,
        ])->id;
    }

    /**
     * @param array<string, mixed> $lien
     */
    private function fiche(array $lien, int $statut): Travail
    {
        return Travail::create([
            'designation' => 'Nettoyage tuyaux',
            'date' => '2096-03-01',
            'date_demande' => '2096-03-01',
            'quantite' => 2,
            'justification' => '',
            'statut' => $statut,
            'sapeur_id' => $this->sapeurId,
            'auteur_id' => $this->sapeurId,
            'travail_type_id' => $this->typeId,
            'exercice_comptable_id' => $this->exerciceComptableId,
            ...$lien,
        ]);
    }

    private function imputerExercice(): void
    {
        ImputationBusiness::imputerExercice($this->exercice->id, ['indemnite_exercice_type_id' => 1]);
    }

    private function imputerIntervention(): void
    {
        ImputationBusiness::imputerIntervention($this->intervention->id, ['indemnite_intervention_type_id' => $this->indemniteInterventionTypeId]);
    }

    public function testImputerExerciceImputeFichesLiees()
    {
        $fiche = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);

        $this->imputerExercice();

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_IMPUTE, $fiche->fresh()->statut);
        $ecritures = Ecriture::where('travail_id', $fiche->id)->orderBy('total')->get();
        $this->assertCount(2, $ecritures);
        $this->assertSame(['10.00', '60.00'], $ecritures->pluck('total')->all());
        foreach ($ecritures as $ecriture) {
            $this->assertSame(ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL, $ecriture->module);
            $this->assertSame($this->exercice->id, $ecriture->exercice_id);
            $this->assertNull($ecriture->intervention_id);
        }
    }

    public function testImputerInterventionImputeFichesLiees()
    {
        $fiche = $this->fiche(['intervention_id' => $this->intervention->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);

        $this->imputerIntervention();

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_IMPUTE, $fiche->fresh()->statut);
        $ecritures = Ecriture::where('travail_id', $fiche->id)->get();
        $this->assertCount(2, $ecritures);
        foreach ($ecritures as $ecriture) {
            $this->assertSame(ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL, $ecriture->module);
            $this->assertSame($this->intervention->id, $ecriture->intervention_id);
        }
    }

    public function testImputerEvenementIgnoreFichesSaisiesEtInvalidees()
    {
        $saisie = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_SAISI);
        $invalidee = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_INVALIDE);

        $this->imputerExercice();

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_SAISI, $saisie->fresh()->statut);
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_INVALIDE, $invalidee->fresh()->statut);
        $this->assertSame(0, Ecriture::whereIn('travail_id', [$saisie->id, $invalidee->id])->count());
    }

    public function testAnnulerImputationExerciceRemetFichesValidees()
    {
        $fiche = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        $this->imputerExercice();

        ImputationBusiness::annulerImputationExercice($this->exercice->id);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $fiche->fresh()->statut);
        $this->assertSame(0, Ecriture::where('travail_id', $fiche->id)->count());
    }

    public function testAnnulerImputationInterventionRemetFichesValidees()
    {
        $fiche = $this->fiche(['intervention_id' => $this->intervention->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        $this->imputerIntervention();

        ImputationBusiness::annulerImputationIntervention($this->intervention->id);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $fiche->fresh()->statut);
        $this->assertSame(0, Ecriture::where('travail_id', $fiche->id)->count());
    }

    public function testFicheLieeExerciceReprendLaDesignationDeLExercice()
    {
        $fiche = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        $this->imputerExercice();

        $exercice = $this->exercice->fresh(['localite']);
        $designationExercice = "{$exercice->localite->designation} ({$exercice->lieu}) {$exercice->designation}";
        $ecritures = Ecriture::where('travail_id', $fiche->id)->get();
        $this->assertCount(2, $ecritures);
        foreach ($ecritures as $ecriture) {
            $this->assertSame($designationExercice, $ecriture->designation);
            $this->assertSame('Rédition - Nettoyage tuyaux', $ecriture->complement);
        }
    }

    public function testFicheLieeInterventionReprendLaDesignationDeLIntervention()
    {
        $fiche = $this->fiche(['intervention_id' => $this->intervention->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        $this->imputerIntervention();

        $intervention = $this->intervention->fresh(['localite', 'typeIntervention']);
        $designationIntervention = "{$intervention->localite->designation} ({$intervention->typeIntervention->designation}) {$intervention->lieu}";
        $ecriture = Ecriture::where('travail_id', $fiche->id)->first();
        $this->assertSame($designationIntervention, $ecriture->designation);
        $this->assertSame('Rédition - Nettoyage tuyaux', $ecriture->complement);
    }

    public function testFicheLibreGardeSaDesignationSansComplement()
    {
        $libre = $this->fiche([], TravauxBusiness::TRAVAIL_STATUT_VALIDE);

        ImputationBusiness::imputerTravaux([$libre->id]);

        $ecriture = Ecriture::where('travail_id', $libre->id)->first();
        $this->assertSame('Rédition - Nettoyage tuyaux', $ecriture->designation);
        $this->assertNull($ecriture->complement);
    }

    public function testImputerTravauxIgnoreFicheLiee()
    {
        $liee = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        $libre = $this->fiche([], TravauxBusiness::TRAVAIL_STATUT_VALIDE);

        ImputationBusiness::imputerTravaux([$liee->id, $libre->id]);

        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_VALIDE, $liee->fresh()->statut);
        $this->assertSame(0, Ecriture::where('travail_id', $liee->id)->count());
        $this->assertSame(TravauxBusiness::TRAVAIL_STATUT_IMPUTE, $libre->fresh()->statut);
        $this->assertSame(2, Ecriture::where('travail_id', $libre->id)->count());
    }

    public function testAnnulerImputationTravailRefuseFicheLiee()
    {
        $fiche = $this->fiche(['exercice_id' => $this->exercice->id], TravauxBusiness::TRAVAIL_STATUT_VALIDE);
        $this->imputerExercice();

        $this->expectException(ArrayException::class);
        ImputationBusiness::annulerImputationTravail($fiche->id);
    }
}
