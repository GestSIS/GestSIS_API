<?php


namespace App\Services;

use App\Business\ImputationBusiness;
use App\Contracts\EcritureRepository;
use App\Contracts\ExerciceRepository;
use App\Contracts\IndemniteExerciceTypeRepository;
use App\Contracts\IndemniteInterventionTypeRepository;

class FraisService
{
    protected $ecritureRepo;
    protected $exerciceRepo;
    protected $indemniteExerice;
    protected $indemniteIntervention;
    protected $business;

    public function __construct(
        EcritureRepository $ecriture,
        ExerciceRepository $exercice,
        IndemniteExerciceTypeRepository $indemniteExercice,
        IndemniteInterventionTypeRepository $indemniteIntervention,
        ImputationBusiness $business)
    {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->indemniteExerice = $indemniteExercice;
        $this->indemniteIntervention = $indemniteIntervention;
        $this->business = $business;
    }

    function generateExercice($exerciceId, $data)
    {

        $indemniteType = $this->indemniteExerice->find($data['indemnite_exercice_type_id']);
        $exercice = $this->exerciceRepo->findWithSapeurs($exerciceId);

        $this->business->imputerExercice($exercice, $indemniteType);

        return $this->ecritureRepo->all();
    }

    function generateIntervention($interventionId, $data)
    {

        $indemniteType = $this->indemniteIntervention->find($data['indemnite_intervention_type_id']);
        $intervention = $this->interventionRepo->findWith($interventionId, ['presences', 'phases']);

        $this->business->imputerIntervention($intervention, $indemniteType);

        return $this->ecritureRepo->all();
    }
}
