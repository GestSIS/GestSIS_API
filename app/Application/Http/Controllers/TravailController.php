<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\TravauxBusiness;
use App\Models\Travail;
use Illuminate\Http\Request;

class TravailController extends Controller
{
    public function index(Request $request, $exerciceComptableId)
    {
        // Auteur
        $perms = $request->attributes->get('permissions', []);
        $hasLectureOuValidationPermission = in_array('fiche_travail.validation', $perms) || in_array('fiche_travail.lecture', $perms);
        $withEcritures = in_array('comptabilite.lecture', $perms);

        $sapeurId = $request->attributes->get('sapeurId');
        if (!$hasLectureOuValidationPermission && !$sapeurId) {
            return response()->json(['error' => ['message' => 'Permissions insuffisantes']], 200);
        }

        $query = Travail::where('exercice_comptable_id', '=', $exerciceComptableId);
        if ($withEcritures) {
            $query = $query->with('ecritures');
        }
        if (!$hasLectureOuValidationPermission && $sapeurId != null) {
            $query = $query->where(function ($query) use ($sapeurId) {
                $query->where('auteur_id', '=', $sapeurId)
                    ->orWhere('sapeur_id', '=', $sapeurId);
            });
        }
        $travaux = $query->get();

        return response()->json(['data' => $travaux]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'travaux' => 'array',
            'travaux.*.designation' => 'required|string|min:1',
            'travaux.*.date' => 'required|date',
            'travaux.*.quantite' => 'required|numeric',
            'travaux.*.sapeur_id' => 'required|integer|exists:sapeurs,id',
            'travaux.*.travail_type_id' => 'required|integer|exists:travail_types,id',
            'travaux.*.exercice_comptable_id' => 'required|integer|exists:exercice_comptables,id',
        ]);

        // Auteur
        $auteurId = $request->attributes->get('sapeurId');
        if (!$auteurId) {
            return response()->json(['error' => ['message' => 'Permissions insuffisantes']], 200);
        }
        $perms = $request->attributes->get('permissions', []);

        $hasSaisieCommunePermission = in_array('fiche_travail.saisie_commune', $perms);

        $travail = TravauxBusiness::ajouter($data['travaux'], $auteurId, $hasSaisieCommunePermission);
        return response()->json(['data' => $travail]);
    }

    public function update(Request $request, $travailId)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'date' => 'required|date',
            'quantite' => 'required|numeric',
            'sapeur_id' => 'required|integer|exists:sapeurs,id',
            'travail_type_id' => 'required|integer|exists:travail_types,id',
        ]);

        $sapeurId = $request->attributes->get('sapeurId', []);

        $travail = TravauxBusiness::modifier($travailId, $data, $sapeurId);
        return response()->json(['data' => $travail]);
    }

    public function review(Request $request, $travailId)
    {
        $data = $request->validate([
            'justification' => 'string|nullable',
            'accepte' => 'boolean|required',
            'quantite' => 'numeric|required',
        ]);

        $travail = TravauxBusiness::review($travailId, $data['accepte'], $data['justification'] ?? '', $data['quantite']);
        return response()->json(['data' => $travail]);
    }

    public function cancelReview(Request $request, $travailId)
    {
        $travail = TravauxBusiness::cancelReview($travailId);
        return response()->json(['data' => $travail]);
    }

    public function destroy(Request $request, $id)
    {
        $sapeurId = $request->attributes->get('sapeurId', []);
        if (!$sapeurId) {
            return response()->json(['error' => ['message' => 'Permissions insuffisantes']], 200);
        }

        $travail = TravauxBusiness::supprimer($id, $sapeurId);
        return response()->json(['data' => $travail]);
    }
}
