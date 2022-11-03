<?php


namespace App\Domaine\API;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\MatPersoBusiness;
use App\Domaine\Business\MatPersoParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\InterventionRepository;
use App\Infrastructure\Models\MaterielAlerte;
use App\Infrastructure\Models\MaterielPersonnel;
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

    public function materiels()
    {
        return MaterielPersonnel::with('materiel')->get();
    }

    public function aRecuperer()
    {
        return MaterielPersonnel::where('retour', '=', null)->whereHas('sapeur', fn ($q) => $q->where('actif', '=', false))->with('materiel')->get();
    }

    public function alertes()
    {
        return MaterielAlerte::with('materiel')->with('materiel')->get();
    }

    public function attribuer($materiels)
    {
        $this->business->attribuer($materiels);
        return $this->materiels();
    }

    public function retour($date, $materielIds)
    {
        $this->business->retour($date, $materielIds);
        return $this->materiels();
    }

    // public function modifierCategorie($id, $data)
    // {
    //     return $this->business->modifierCategorie($id, $data);
    // }

    // public function supprimerCategorie($id)
    // {
    //     return $this->business->supprimerCategorie($id);
    // }
}
