<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ImputationBusiness;

class AmendeSapeurController extends Controller
{
    public function sapeur(int $exerciceComptableId, int $sapeurId)
    {
        $ecritures = ImputationBusiness::genererAmendesSapeur($exerciceComptableId, $sapeurId);
        return response()->json(['data' => $ecritures]);
    }

    public function annuel(int $exerciceComptableId)
    {
        $ecritures = ImputationBusiness::genererAmendesAnnuels($exerciceComptableId);
        return response()->json(['data' => $ecritures]);
    }
}
