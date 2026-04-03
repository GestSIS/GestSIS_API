<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceBusiness;
use App\Models\HeureExercice;
use Illuminate\Http\Request;

class HeureExerciceController extends Controller
{

    public function index($exerciceId)
    {
        return HeureExercice::where('exercice_id', '=', $exerciceId)->get();
    }

    public function store(Request $request, $exerciceId)
    {
        $data = $request->validate([
            'montant' => 'numeric|require',
            'type' => 'integer|require',
            'sapeur_id' => 'integer|exists:sapeurs,id|require',
            'heure_exercice_type_id' => 'integer|exists:heure_exercice_types,id',
        ]);
        $heure = ExerciceBusiness::ajouterHeureExercice($exerciceId, $data);
        return response()->json(['data' => $heure]);
    }

    public function update(Request $request, $exerciceId, $id)
    {
        $data = $request->validate([
            'montant' => 'numeric',
        ]);
        $heure = ExerciceBusiness::modifierHeureExercice($exerciceId, $id, $data);
        return response()->json(['data' => $heure]);
    }

    public function destroy($exerciceId, $id)
    {
        $heure = ExerciceBusiness::supprimerHeureExercice($exerciceId, $id);
        return response()->json(['data' => $heure]);
    }
}
