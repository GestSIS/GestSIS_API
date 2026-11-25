<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class RecrueController extends Controller
{
    /**
     * Validation d'une recrue (aucune mutation) : crée sa première mutation sapeur.
     * Le listing et le rejet réutilisent respectivement l'index de SapeurController
     * et sa route destroy existante (SapeurBusiness::deleteSapeurById gère déjà la suppression cascade).
     */
    public function valider(Request $request, int $id)
    {
        $data = $request->validate([
            'incorporation' => 'required|date',
        ]);

        $isRecrue = Sapeur::whereId($id)
            ->where('statut_sapeur', SapeurBusiness::STATUT_JAMAIS)
            ->where('statut_civil', SapeurBusiness::STATUT_JAMAIS)
            ->exists();
        if (!$isRecrue) {
            return response()->json(['message' => 'Recrue non trouvée'], 404);
        }

        $sapeur = SapeurBusiness::validateRecrue($id, $data['incorporation']);
        return response()->json(['data' => [
            ...$sapeur->toArray(),
            'type' => SapeurBusiness::typePrincipal($sapeur->statut_sapeur, $sapeur->statut_civil),
        ]]);
    }
}
