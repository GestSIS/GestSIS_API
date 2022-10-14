<?php


namespace App\Domaine\API;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\MatPersoBusiness;
use App\Domaine\Business\MatPersoParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\InterventionRepository;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Illuminate\Support\Facades\Http;

class MatPersoService
{
    protected $business;

    public function __construct(MatPersoBusiness $business)
    {
        $this->business = $business;
    }

    // public function categories()
    // {
    //     return ExerciceCategorie::all();
    // }

    // public function ajouterCategorie($data)
    // {
    //     return $this->business->ajouterCategorie($data);
    // }

    // public function modifierCategorie($id, $data)
    // {
    //     return $this->business->modifierCategorie($id, $data);
    // }

    // public function supprimerCategorie($id)
    // {
    //     return $this->business->supprimerCategorie($id);
    // }
}
