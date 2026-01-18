<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\GroupeSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class SapeurGroupeController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $groupes = $this->service->getSapeurGroupesById($sapeurId);

        return response()->json(['data' => $groupes]);
    }

    public function quitter(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'groupes.*' => 'required|integer'
        ]);

        $groupes = $this->service->finGroupes($sapeurId, $data['groupes']);

        return response()->json(['data' => $groupes]);
    }
}
