<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ControleMedicalBusiness;
use App\Models\Medecin;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Medecin::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'adresse' => 'string|min:0|nullable',
            'localite_id' => 'integer|required',
            'actif' => 'boolean|required'
        ]);

        $medecin = ControleMedicalBusiness::ajouterMedecin($data);
        return response()->json(['data' => $medecin]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'adresse' => 'string|min:0',
            'localite_id' => 'integer',
            'actif' => 'boolean'
        ]);

        $medecin = ControleMedicalBusiness::modifierMedecin($id, $data);
        return response()->json(['data' => $medecin]);
    }

    public function destroy($id)
    {
        $medecin = ControleMedicalBusiness::supprimerMedecin($id);
        return response()->json(['data' => $medecin]);
    }
}
