<?php

namespace App\Application\Http\Controllers;

use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\HeureExercice;
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
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $heures = HeureExercice::where('sapeur_id', '=', $sapeurId)->get()->toArray();
        $sapeurs = ExerciceSapeur::where('sapeur_id', '=', $sapeurId)->get()->toArray();

        $exercices = Exercice::where('exercice_comptable_id', '=', $exerciceComptableId)
            ->whereIn('id', array_merge(
                array_map(fn($h) => $h['exercice_id'], $heures),
                array_map(fn($h) => $h['exercice_id'], $sapeurs),
            ))->get()->toArray();

        $dictionary = [];
        foreach ($exercices as $exercice) {
            $dictionary[$exercice['id']] = $exercice;
            $dictionary[$exercice['id']]['heures'] = [];
            $dictionary[$exercice['id']]['presence'] = null;
        }

        foreach ($sapeurs as $sapeur) {
            if (array_key_exists($sapeur['exercice_id'], $dictionary)) {
                $dictionary[$sapeur['exercice_id']]['presence'] = $sapeur;
            }
        }
        foreach ($heures as $heure) {
            if (array_key_exists($heure['exercice_id'], $dictionary)) {
                $dictionary[$heure['exercice_id']]['heures'][] = $heure;
            }
        }

        $data = array_values($dictionary);
        return response()->json(['data' => $data]);
    }
}
