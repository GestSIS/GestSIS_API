<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class RecrueController extends Controller
{
    /**
     * Validation d'une recrue : bascule son type en sapeur réel et crée sa première mutation.
     * Le listing et le rejet réutilisent respectivement l'index de SapeurController (filtre `type`)
     * et sa route destroy existante (SapeurBusiness::deleteSapeurById gère déjà la suppression cascade).
     */
    public function valider(Request $request, int $id)
    {
        $data = $request->validate([
            'incorporation' => 'required|date',
        ]);

        if (!Sapeur::where('type', SapeurBusiness::TYPE_RECRUE)->where('id', $id)->exists()) {
            return response()->json(['error' => 'Recrue non trouvée'], 404);
        }

        $sapeur = SapeurBusiness::validateRecrue($id, $data['incorporation']);
        return response()->json(['data' => $sapeur->toArray()]);
    }
}
