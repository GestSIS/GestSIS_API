<?php

namespace App\Application\Http\Controllers;

use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\Travail;
use Illuminate\Http\Request;

class MesExercicesController extends Controller
{

    /**
     * Récupération des exercices du sapeur
     */
    public function index(Request $request, $exerciceComptableId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['message' => 'Votre compte n\'est pas lié à un sapeur'], 422);
        }

        // Fiches de travail du sapeur liées à un exercice (remplacent les heures supplémentaires)
        $travaux = Travail::where('sapeur_id', '=', $sapeurId)->whereNotNull('exercice_id')->get()->toArray();
        $sapeurs = ExerciceSapeur::where('sapeur_id', '=', $sapeurId)->get()->toArray();

        $exercices = Exercice::where('exercice_comptable_id', '=', $exerciceComptableId)
            ->whereIn('id', array_merge(
                array_map(fn($t) => $t['exercice_id'], $travaux),
                array_map(fn($h) => $h['exercice_id'], $sapeurs),
            ))->get()->toArray();

        $dictionary = [];
        foreach ($exercices as $exercice) {
            $dictionary[$exercice['id']] = $exercice;
            $dictionary[$exercice['id']]['travaux'] = [];
            // Compatibilité : l'appli mobile (versions antérieures aux fiches de travail) lit encore `heures`
            $dictionary[$exercice['id']]['heures'] = [];
            $dictionary[$exercice['id']]['presence'] = null;
        }

        foreach ($sapeurs as $sapeur) {
            if (array_key_exists($sapeur['exercice_id'], $dictionary)) {
                $dictionary[$sapeur['exercice_id']]['presence'] = $sapeur;
            }
        }
        foreach ($travaux as $travail) {
            if (array_key_exists($travail['exercice_id'], $dictionary)) {
                $dictionary[$travail['exercice_id']]['travaux'][] = $travail;
            }
        }

        $data = array_values($dictionary);
        return response()->json(['data' => $data]);
    }
}
