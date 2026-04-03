<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurMutationController extends Controller
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
        return response()->json(['data' => $this->repo->getSapeurMutationsById($sapeurId)]);
    }

    public function store(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'incorporation' => 'required|date',
            'sortie' => 'date|nullable|after:incorporation',
            'motif' => 'string|nullable',
            'localite_id' => 'required|integer|exists:localites,id',
        ]);

        $mutation = $this->business->addMutation($sapeurId, $data);
        return response()->json(['data' => $mutation]);
    }

    public function update(Request $request, int $sapeurId, int $mutationId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'id' => 'required|integer',
            'incorporation' => 'date',
            'sortie' => 'date|nullable|after:incorporation',
            'motif' => 'string|nullable',
            'localite_id' => 'integer|exists:localites,id',
        ]);

        if ($mutationId !== $request->get('id')) {
            return response()->json(['error' => 'invalid mutation id'], 400);
        }

        $mutations = $this->repo->getSapeurMutationsById($sapeurId);
        if (!collect($mutations)->firstWhere('id', $mutationId)) {
            return response()->json(['error' => 'Mutation non trouvée'], 404);
        }

        $mutation = $this->business->updateMutation($sapeurId, $data);
        return response()->json(['data' => $mutation]);
    }

    public function destroy(int $sapeurId, int $mutationId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $mutations = $this->repo->getSapeurMutationsById($sapeurId);
        if (!collect($mutations)->firstWhere('id', $mutationId)) {
            return response()->json(['error' => 'Mutation non trouvée'], 404);
        }

        $data = $this->business->removeMutation($sapeurId, $mutationId);
        return response()->json(['data' => $data]);
    }
}
