<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MesInfosService;
use Illuminate\Http\Request;

class MesInfosController extends Controller
{

    public function __construct(MesInfosService $service)
    {
        $this->service = $service;
    }

    /**
     * Récupère les informations du sapeur
     */
    public function index(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesInfos($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function fonctions(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesFonctions($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function mutations(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesMutations($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function grades(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesGrades($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function cours(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesCours($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function groupes(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesGroupes($sapeurId);
        return response()->json(['data' => $data]);
    }
}
