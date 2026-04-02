<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurParamBusiness;
use App\Infrastructure\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::all();

        return response()->json(['data' => $grades]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'abreviation' => 'string|min:1',
            'groupe' => 'integer|min:1',
            'tri' => 'integer'
        ]);

        $grade = SapeurParamBusiness::ajouterGrade($data);
        return response()->json(['data' => $grade]);
    }

    public function update(Request $request, $id)
    {
        if (!Grade::where('id', $id)->exists()) {
            return response()->json(['error' => 'Grade not found'], 404);
        }

        $data = $request->validate([
            'designation' => 'string|min:1',
            'abreviation' => 'string|min:1',
            'groupe' => 'integer|min:1',
            'tri' => 'integer'
        ]);

        $grade = SapeurParamBusiness::modifierGrade($id, $data);
        return response()->json(['data' => $grade]);
    }

    public function destroy($id)
    {
        if (!Grade::where('id', $id)->exists()) {
            return response()->json(['error' => 'Grade not found'], 404);
        }

        $grade = SapeurParamBusiness::supprimerGrade($id);
        return response()->json(['data' => $grade]);
    }
}
