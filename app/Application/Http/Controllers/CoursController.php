<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurParamBusiness;
use App\Models\Cours;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    public function index()
    {
        $cours = Cours::all();

        return response()->json(['data' => $cours]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'abreviation' => 'string|min:1|required',
            'designation' => 'string|min:1|required',
            'validite_debut' => 'date|nullable',
            'validite_fin' => 'date|nullable',
            'fonction_id' => 'integer|nullable',
            'grade_id' => 'integer|nullable',
            'precedent_id' => 'integer|nullable',
            'duree' => 'numeric|required|min:0',
            'tri' => 'integer|required',
        ]);

        $cours = SapeurParamBusiness::ajouterCours($data);
        return response()->json(['data' => $cours]);
    }

    public function update(Request $request, $id)
    {
        if (!Cours::where('id', $id)->exists()) {
            return response()->json(['error' => 'Cours not found'], 404);
        }

        $data = $request->validate([
            'abreviation' => 'string|min:1',
            'designation' => 'string|min:1',
            'validite_debut' => 'date|nullable',
            'validite_fin' => 'date|nullable',
            'fonction_id' => 'integer|nullable',
            'grade_id' => 'integer|nullable',
            'precedent_id' => 'integer|nullable',
            'duree' => 'numeric|min:0|nullable',
            'tri' => 'integer',
        ]);

        $cours = SapeurParamBusiness::modifierCours($id, $data);
        return response()->json(['data' => $cours]);
    }

    public function destroy($id)
    {
        if (!Cours::where('id', $id)->exists()) {
            return response()->json(['error' => 'Cours not found'], 404);
        }

        $cours = SapeurParamBusiness::supprimerCours($id);
        return response()->json(['data' => $cours]);
    }
}
