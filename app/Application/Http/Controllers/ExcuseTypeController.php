<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceParamBusiness;
use App\Models\ExcuseType;
use Illuminate\Http\Request;

class ExcuseTypeController extends Controller
{
    public function index()
    {
        $excusesTypes = ExcuseType::all();

        return response()->json(['data' => $excusesTypes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'abreviation' => 'string|min:1|required',
            'amende' => 'boolean|required',
            'statut' => 'integer|required',
            'tri' => 'integer'
        ]);

        $excuseType = ExerciceParamBusiness::ajouterExcuseType($data);
        return response()->json(['data' => $excuseType]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'abreviation' => 'string|min:1',
            'amende' => 'boolean',
            'statut' => 'integer',
            'tri' => 'integer'
        ]);

        $excuseType = ExerciceParamBusiness::modifierExcuseType($id, $data);
        return response()->json(['data' => $excuseType]);
    }

    public function destroy($id)
    {
        ExerciceParamBusiness::supprimerExcuseType($id);
        return response()->json(['data' => 'ok']);
    }
}
