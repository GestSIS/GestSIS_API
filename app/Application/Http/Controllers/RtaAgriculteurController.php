<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\RtaBusiness;
use Illuminate\Http\Request;

class RtaAgriculteurController extends Controller
{
    public function index()
    {
        return response()->json(RtaBusiness::getAgriculteurs());
    }

    public function show($agriculteurId)
    {
        $agriculteur = RtaBusiness::getAgriculteur($agriculteurId);
        if (!$agriculteur) {
            return response()->json(["message" => "Agriculteur non trouvé"], 404);
        }

        return response()->json($agriculteur);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agriculteur' => 'required|string|max:50',
            'communes' => 'required|string|max:100',
            'lieudit' => 'string|nullable|max:50',
            'capacites' => 'required|array|min:1',
            'capacites.*.capacite' => 'required|integer',
            'moyens_contact' => 'required|array|min:1',
            'moyens_contact.*.tri' => 'required|integer',
            'moyens_contact.*.type' => 'required|in:Mobile,Privé,Prof',
            'moyens_contact.*.numero' => 'required|string|max:20',
        ]);
        $data['lieudit'] = $data['lieudit'] ?? '';

        $created = RtaBusiness::createAgriculteur($data);
        if (!$created) {
            return response()->json(["message" => "Erreur lors de la création de l'agriculteur"], 500);
        }

        return response()->json($created, 201);
    }

    public function update($agriculteurId, Request $request)
    {
        $data = $request->validate([
            'agriculteur' => 'required|string|max:50',
            'communes' => 'required|string|max:100',
            'lieudit' => 'string|nullable|max:50',
            'capacites' => 'required|array|min:1',
            'capacites.*.id' => 'nullable|integer',
            'capacites.*.capacite' => 'required|integer',
            'moyens_contact' => 'required|array|min:1',
            'moyens_contact.*.id' => 'nullable|integer',
            'moyens_contact.*.tri' => 'required|integer',
            'moyens_contact.*.type' => 'required|in:Mobile,Privé,Prof',
            'moyens_contact.*.numero' => 'required|string|max:20',
        ]);
        $data['lieudit'] = $data['lieudit'] ?? '';

        $updated = RtaBusiness::updateAgriculteur($agriculteurId, $data);
        if (!$updated) {
            return response()->json(["message" => "Agriculteur non trouvé"], 404);
        }

        return response()->json($updated);
    }

    public function tri(Request $request, int $agriculteurId)
    {
        $data = $request->validate([
            'tri' => 'required|integer|min:0',
        ]);

        $updated = RtaBusiness::updateAgriculteurTri($agriculteurId, $data);
        if (!$updated) {
            return response()->json(["message" => "Agriculteur non trouvé"], 404);
        }

        return response()->json($updated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $agriculteurId)
    {
        $deleted = RtaBusiness::deleteAgriculteur($agriculteurId);
        if (!$deleted) {
            return response()->json(["message" => "Agriculteur non trouvé"], 404);
        }

        return response()->json(null, 204);
    }

}
