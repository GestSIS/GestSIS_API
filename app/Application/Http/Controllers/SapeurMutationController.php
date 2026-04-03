<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Mutation;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurMutationController extends Controller
{

    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => Mutation::where('sapeur_id', $sapeurId)->get()]);
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

        $mutation = SapeurBusiness::addMutation($sapeurId, $data);
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

        if ($mutationId !== $request->input('id')) {
            return response()->json(['error' => 'invalid mutation id'], 400);
        }

        if (!Mutation::where('sapeur_id', $sapeurId)->where('id', $mutationId)->exists()) {
            return response()->json(['error' => 'Mutation non trouvée'], 404);
        }

        $mutation = SapeurBusiness::updateMutation($sapeurId, $data);
        return response()->json(['data' => $mutation]);
    }

    public function destroy(int $sapeurId, int $mutationId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        if (!Mutation::where('sapeur_id', $sapeurId)->where('id', $mutationId)->exists()) {
            return response()->json(['error' => 'Mutation non trouvée'], 404);
        }

        $data = SapeurBusiness::removeMutation($sapeurId, $mutationId);
        return response()->json(['data' => $data]);
    }
}
