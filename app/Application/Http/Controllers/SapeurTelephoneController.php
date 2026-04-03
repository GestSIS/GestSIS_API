<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use Illuminate\Http\Request;

class SapeurTelephoneController extends Controller
{

    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => SapeurTelephone::where('sapeur_id', $sapeurId)->get()]);
    }

    public function store(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'telephone_type_id' => 'required|integer|exists:telephone_types,id',
            'numero' => 'required|string|min:2',
            'priorite' => 'required|integer',
            'rta' => 'required|boolean',
        ]);

        $telephone = SapeurBusiness::addTelephone($sapeurId, $data);
        return response()->json(['data' => $telephone]);
    }

    public function update(Request $request, int $sapeurId, int $telephoneId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'id' => 'required|integer',
            'telephone_type_id' => 'integer|exists:telephone_types,id',
            'numero' => 'string|min:2',
            'priorite' => 'integer',
            'rta' => 'boolean',
        ]);

        if ($telephoneId !== $request->input('id')) {
            return response()->json(['error' => 'invalid telephone id'], 400);
        }
        if (!SapeurTelephone::where(['id' => $telephoneId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Téléphone non trouvé'], 404);
        }

        $telephone = SapeurBusiness::updateTelephone($sapeurId, $data);
        return response()->json(['data' => $telephone]);
    }

    public function destroy(int $sapeurId, int $telephoneId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!SapeurTelephone::where(['id' => $telephoneId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Téléphone non trouvé'], 404);
        }

        SapeurBusiness::removeTelephone($sapeurId, $telephoneId);
        return response()->json(['data' => 'success']);
    }
}
