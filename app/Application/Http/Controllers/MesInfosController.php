<?php

namespace App\Application\Http\Controllers;

use App\Models\CoursSapeur;
use App\Models\FonctionSapeur;
use App\Models\GradeSapeur;
use App\Models\GroupeSapeur;
use App\Models\Mutation;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class MesInfosController extends Controller
{

    /**
     * Récupère les informations du sapeur
     */
    public function index(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = Sapeur::with(['telephones'])->find($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function fonctions(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = FonctionSapeur::where('sapeur_id', $sapeurId)->get();
        return response()->json(['data' => $data]);
    }

    public function mutations(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = Mutation::where('sapeur_id', $sapeurId)->get();
        return response()->json(['data' => $data]);
    }

    public function grades(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = GradeSapeur::where('sapeur_id', $sapeurId)->get();
        return response()->json(['data' => $data]);
    }

    public function cours(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = CoursSapeur::where('sapeur_id', $sapeurId)->get();
        return response()->json(['data' => $data]);
    }

    public function permis(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = Sapeur::find($sapeurId)->permis()->get();
        return response()->json(['data' => $data]);
    }

    public function groupes(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = GroupeSapeur::where('sapeur_id', $sapeurId)->get();
        return response()->json(['data' => $data]);
    }
}
