<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurStatistiqueController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
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
    public function civilite(int $exerciceComptableId)
    {
        $data = $this->service->statCivilite($exerciceComptableId);

        return response()->json(['data' => $data]);
    }

    public function fonction($exerciceComptableId)
    {
        $data = $this->service->statFonction($exerciceComptableId);

        return response()->json(['data' => $data]);
    }

    public function grade($exerciceComptableId)
    {
        $materiels = $this->service->statGrade($exerciceComptableId);

        return response()->json(['data' => $materiels]);
    }

    public function permis($exerciceComptableId)
    {
        $materiels = $this->service->statPermis($exerciceComptableId);

        return response()->json(['data' => $materiels]);
    }
}
