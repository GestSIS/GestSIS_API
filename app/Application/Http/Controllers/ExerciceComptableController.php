<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceComptableBusiness;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;

class ExerciceComptableController extends Controller
{
    public function index()
    {
        $exerciceComptables = ExerciceComptable::all();

        return response()->json(['data' => $exerciceComptables]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'annee' => 'required|integer',
            'designation' => 'required|string|min:1',
            'debut' => 'required|date',
            'fin' => 'required|date',
            'boucle' => 'integer'
        ]);

        $exercice = ExerciceComptableBusiness::creerExerciceComptable($data);
        return response()->json(['data' => $exercice]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'annee' => 'integer',
            'designation' => 'string|min:1',
            'debut' => 'date',
            'fin' => 'date',
            'boucle' => 'integer',
        ]);

        $exercice = ExerciceComptableBusiness::modifierExerciceComptable($id, $data);
        return response()->json(['data' => $exercice]);
    }

    public function destroy($id)
    {
        $exercice = ExerciceComptableBusiness::supprimerExerciceComptable($id);
        return response()->json(['data' => $exercice]);
    }

    public function cloturer($id)
    {
        $exercice = ExerciceComptableBusiness::cloturerExerciceComptable($id);
        return response()->json(['data' => $exercice]);
    }
}
