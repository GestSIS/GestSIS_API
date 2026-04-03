<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\FonctionSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurFonctionController extends Controller
{
    protected $repo;
    protected $business;

    public function __construct(SapeurRepository $repo, SapeurBusiness $business)
    {
        $this->repo = $repo;
        $this->business = $business;
    }

    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => $this->repo->getSapeurFonctionsById($sapeurId)]);
    }

    public function store(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'fonction_id' => 'required|integer|exists:fonctions,id',
            'debut' => 'required|date',
            'fin' => 'date|nullable|after_or_equal:debut',
            'remarque' => 'string|nullable',
        ]);

        $fonction = $this->business->addFonction($sapeurId, $data);
        return response()->json(['data' => $fonction]);
    }

    public function update(Request $request, int $sapeurId, int $fonctionId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!FonctionSapeur::where(['id' => $fonctionId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Fonction non trouvée'], 404);
        }
        if ($fonctionId !== $request->get('id')) {
            return response()->json(['error' => 'invalid fonction id']);
        }

        $data = $request->validate([
            'id' => 'required|integer|exists:fonction_sapeur,id',
            'debut' => 'date',
            'fin' => 'date|nullable|after:debut',
            'remarque' => 'string|nullable',
        ]);

        $fonction = $this->business->updateFonction($sapeurId, $data);
        return response()->json(['data' => $fonction]);
    }

    public function destroy(int $sapeurId, int $fonctionId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!FonctionSapeur::where(['id' => $fonctionId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Fonction non trouvée'], 404);
        }

        $res = $this->business->removeFonction($sapeurId, $fonctionId);
        return response()->json(['data' => $res]);
    }

    public function fin(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'ids.*' => 'required|integer',
            'date' => 'required|date'
        ]);

        $fonctions = $this->business->finFonctions($sapeurId, $data['date'], $data['ids']);
        return response()->json(['data' => $fonctions]);
    }
}
