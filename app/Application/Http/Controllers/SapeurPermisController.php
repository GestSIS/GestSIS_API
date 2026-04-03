<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Permis;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurPermisController extends Controller
{

    public function index(int $sapeur_id)
    {
        if (!Sapeur::where('id', $sapeur_id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => Sapeur::find($sapeur_id)->permis()->get()]);
    }

    public function store(Request $request, int $id)
    {
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'permis_type_id' => 'required|integer|exists:permis_types,id',
            'date' => 'required|date|before:tomorrow'
        ]);

        $permis = SapeurBusiness::addPermis($id, $data);
        return response()->json(['data' => $permis]);
    }

    public function update(Request $request, int $id, int $permisId)
    {
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'id' => 'required|integer',
            'date' => 'required|date|before:tomorrow'
        ]);

        if ($permisId !== $request->get('id')) {
            return response()->json(['error' => 'invalid permis id'], 400);
        }
        if (!Permis::where(['id' => $permisId, 'sapeur_id' => $id])->exists()) {
            return response()->json(['error' => 'Permis non trouvé'], 404);
        }

        $permis = SapeurBusiness::updatePermis($id, $data);
        return response()->json(['data' => $permis]);
    }

    public function destroy(int $id, int $permisId)
    {
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!Permis::where(['id' => $permisId, 'sapeur_id' => $id])->exists()) {
            return response()->json(['error' => 'Permis non trouvé'], 404);
        }

        SapeurBusiness::removePermis($id, $permisId);
        return response()->json(['data' => 'success']);
    }
}
