<?php


namespace App\Services;

use App\Business\ImputationBusiness;
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
    protected $business;

    public function __construct(
        EcritureRepository $ecriture,
        ExerciceRepository $exercice,
        IndemniteTypeRepository $indemnite,
        FraisTypeRepository $frais,
        ImputationBusiness $business)
    {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->indemniteRepo = $indemnite;
        $this->fraisRepo = $frais;
        $this->business = $business;
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
        return $this->ecritureRepo->getEcrituresForExerciceById($exerciceId);
    }

    function getEcrituresForInterventionById($interventionId)
    {
        return $this->ecritureRepo->getEcrituresForInterventionById($interventionId);
    }

    function getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId)
    {
        return $this->ecritureRepo->getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);
    }

    function imputationExercice($exerciceId, $data)
    {
        $this->business->imputerExercice($exerciceId, $data);

        return $this->ecritureRepo->listeEcritureForExercice($exerciceId);
    }

    function imputationIntervention($interventionId, $data)
    {
        $this->business->imputerIntervention($interventionId, $data);

        return $this->ecritureRepo->listeEcritureForIntervention($interventionId);
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
