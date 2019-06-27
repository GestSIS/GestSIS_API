<?php


namespace App\Services;

use App\Business\ImputationBusiness;
use App\Contracts\EcritureRepository;
use App\Contracts\ExerciceRepository;
use App\Contracts\IndemniteTypeRepository;

class FraisService
{
    protected $ecritureRepo;
    protected $exerciceRepo;
    protected $indemniteRepo;
    protected $business;

    public function __construct(
        EcritureRepository $ecriture,
        ExerciceRepository $exercice,
        IndemniteTypeRepository $indemnite,
        ImputationBusiness $business)
    {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->indemniteRepo = $indemnite;
        $this->business = $business;
    }

    function generateExercice($exerciceId, $data)
    {
        $this->business->imputerExercice($exerciceId, $data);

        return $this->ecritureRepo->listeEcritureForExercice($exerciceId);
    }

    function generateIntervention($interventionId, $data)
    {
        $this->business->imputerIntervention($interventionId, $data);

        return $this->ecritureRepo->listeEcritureForIntervention($interventionId);
    }
}
