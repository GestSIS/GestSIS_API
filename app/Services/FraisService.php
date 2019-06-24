<?php


namespace App\Services;

use App\Business\ImputationBusiness;
use App\Contracts\EcritureRepository;
use App\Contracts\ExerciceRepository;
use App\Contracts\IndemniteExerciceTypeRepository;

class FraisService
{
    protected $ecritureRepo;
    protected $exerciceRepo;
    protected $indemniteExerice;
    protected $business;

    public function __construct(EcritureRepository $ecriture, ExerciceRepository $exercice, IndemniteExerciceTypeRepository $indemniteExercice, ImputationBusiness $business)
    {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->indemniteExerice = $indemniteExercice;
        $this->business = $business;
    }

    function generateExercice($exerciceId, $data)
    {

        $indemniteType = $this->indemniteExerice->find($data['indemnite_exercice_type_id']);
        $exercice = $this->exerciceRepo->findWithSapeurs($exerciceId);

        $this->business->imputerExercice($exercice, $indemniteType);

        return $this->ecritureRepo->all();
    }
}
