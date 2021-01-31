<?php


namespace App\Domaine\API;

use App\Domaine\Business\ImputationBusiness;
use App\Domaine\SPI\CompteRepository;
use App\Domaine\SPI\EcritureRepository;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\FraisTypeRepository;
use App\Domaine\SPI\IndemniteTypeRepository;
use PDF;

class ComptabiliteService
{
    protected $ecritureRepo;
    protected $exerciceRepo;
    protected $indemniteRepo;
    protected $fraisRepo;
    protected $compteRepo;
    protected $business;

    public function __construct(
        EcritureRepository $ecriture,
        ExerciceRepository $exercice,
        IndemniteTypeRepository $indemnite,
        FraisTypeRepository $frais,
        CompteRepository $comptes,
        ImputationBusiness $business)
    {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->indemniteRepo = $indemnite;
        $this->fraisRepo = $frais;
        $this->compteRepo = $comptes;
        $this->business = $business;
    }

    function getComptes()
    {
        return $this->compteRepo->listComptes();
    }

    function getAllEcrituresForExerciceComptableById($exerciceComptableId)
    {
        return $this->ecritureRepo->listeAllEcritureForExerciceComptableById($exerciceComptableId);
    }

    function getEcrituresAmendesForExerciceComptableById($exerciceComptableId)
    {
        return $this->ecritureRepo->listeAmendeForExerciceComptableById($exerciceComptableId);
    }

    function getEcrituresByCompte($compteId, $exerciceComptableId)
    {
        return $this->ecritureRepo->listeEcritureForCompteAndExerciceComptableById($compteId, $exerciceComptableId);
    }

    function getIndemnitesTypes()
    {
        return array(
            "exercices" => $this->indemniteRepo->listeIndemniteExerciceType(),
            "interventions" => $this->indemniteRepo->listeIndemniteInterventionType(),
            "annuels" => $this->indemniteRepo->listeIndemniteAnnuelType(),
        );
    }

    function getFraisTypes()
    {
        return array(
            "annuels" => $this->fraisRepo->listeFraisAnnuelType()
        );
    }

    function getEcrituresForExerciceById($exerciceId)
    {
        return $this->ecritureRepo->listeEcritureForExercice($exerciceId);
    }

    function getEcrituresForInterventionById($interventionId)
    {
        return $this->ecritureRepo->listeEcritureForIntervention($interventionId);
    }

    function getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId)
    {
        return $this->ecritureRepo->listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);
    }

    function imputationExercice($exerciceId, $data)
    {
        $statut = $this->business->imputerExercice($exerciceId, $data);

        return [
            "statut" => $statut,
            "ecritures" => $this->ecritureRepo->listeEcritureForExercice($exerciceId)
        ];
    }

    function imputationIntervention($interventionId, $data)
    {
        $statut = $this->business->imputerIntervention($interventionId, $data);

        return [
            "statut" => $statut,
            "ecritures" => $this->ecritureRepo->listeEcritureForIntervention($interventionId)
        ];
    }

    function imputationAnnuel($exerciceComptableId)
    {
        $this->business->imputerAnnuel($exerciceComptableId);

        return [
            "frais" => $this->ecritureRepo->listeFraisAnnuelByExeComptableId($exerciceComptableId),
            "indemnites" => $this->ecritureRepo->listeIndemniteAnnuelByExeComptableId($exerciceComptableId),
        ];
    }

    function genererAmendeSapeur($exerciceComptableId, $sapeurId)
    {
        return $this->business->genererAmendeSapeur($exerciceComptableId, $sapeurId);
    }

    function genererAmendeAnnuel($exerciceComptableId)
    {
        return $this->business->genererAmendeAnnuels($exerciceComptableId);
    }

    function decompteAnnuelParSapeur($exerciceComptableId)
    {
        $ecritures = $this->ecritureRepo->computeEcritureForPersonalDecompte($exerciceComptableId);

//        return View('pdf/decomptes-sapeurs', ["ecritures"=>$ecritures]);
        $pdf = PDF::loadView('pdf/decomptes-sapeurs', ["ecritures" => $ecritures]);
        return $pdf->download('invoice.pdf');
    }
}
