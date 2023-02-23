<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionStatistiqueController extends Controller
{
    protected $service;

    public function __construct(InterventionService $service)
    {
        $this->service = $service;
    }

    /**
     * Return le nombre d'intervention par materiel pour l'année comptable
     *
     * @param Request $request
     * @param int $exercice_comptable_id
     * @return Response
     */
    public function materiel(int $exerciceComptableId)
    {
        $data = $this->service->statMateriel($exerciceComptableId);

        return response()->json(['data' => $data]);
    }


    public function vehicule($exerciceComptableId)
    {
        $data = $this->service->statVehicule($exerciceComptableId);

        return response()->json(['data' => $data]);
    }


    public function typeIntervention($exerciceComptableId)
    {
        $materiels = $this->service->statTypeIntervention($exerciceComptableId);

        return response()->json(['data' => $materiels]);
    }


    public function statFederal($exerciceComptableId)
    {
        $materiels = $this->service->statFederal($exerciceComptableId);

        return response()->json(['data' => $materiels]);
    }


    public function traitement($exerciceComptableId)
    {
        $materiels = $this->service->statTraitement($exerciceComptableId);

        return response()->json(['data' => $materiels]);
    }
}
