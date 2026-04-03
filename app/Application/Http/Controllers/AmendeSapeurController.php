<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ImputationBusiness;

class AmendeSapeurController extends Controller
{
    protected $business;

    public function __construct(ImputationBusiness $business)
    {
        $this->business = $business;
    }

    public function sapeur(int $exerciceComptableId, int $sapeurId)
    {
        $ecritures = $this->business->genererAmendesSapeur($exerciceComptableId, $sapeurId);
        return response()->json(['data' => $ecritures]);
    }

    public function annuel(int $exerciceComptableId)
    {
        $ecritures = $this->business->genererAmendesAnnuels($exerciceComptableId);
        return response()->json(['data' => $ecritures]);
    }
}
