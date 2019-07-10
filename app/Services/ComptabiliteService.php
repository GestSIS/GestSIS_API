<?php


namespace App\Services;

use App\Business\ImputationBusiness;
use App\Contracts\CompteRepository;
use App\Contracts\EcritureRepository;
use App\Contracts\ExerciceRepository;
use App\Contracts\FraisTypeRepository;
use App\Contracts\IndemniteTypeRepository;

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

        dd($this->ecritureRepo->listeEcritureForIntervention($interventionId));

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
}
