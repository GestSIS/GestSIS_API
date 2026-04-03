<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ControleMedicalBusiness;
use App\Infrastructure\Models\ControleMedical;
use Illuminate\Http\Request;

class ControleMedicalController extends Controller
{
    public function index()
    {
        return response()->json(['data' => ControleMedical::all()]);
    }

    public function show(int $id)
    {
        return response()->json(['data' => ControleMedical::find($id)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sapeur_id' => 'integer|exists:sapeurs,id',
            'medecin_id' => 'integer|exists:medecins,id',
            'controle_medical_type_id' => 'integer|exists:controle_medical_types,id',
            'consultation' => 'date',
            'validite' => 'nullable|date|after:consultation',
            'designation' => 'string|nullable',
            'en_cours' => 'boolean',
            'accepter' => 'boolean'
        ]);

        $controle = ControleMedicalBusiness::createControleMedical($data);

        return response()->json(['data' => $controle]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'id' => 'integer',
            'medecin_id' => 'integer',
            'controle_medical_type_id' => 'integer',
            'consultation' => 'date',
            'validite' => 'nullable|date|after:consultation',
            'designation' => 'string|nullable',
            'en_cours' => 'boolean',
            'accepter' => 'boolean'
        ]);

        $controle = ControleMedicalBusiness::updateControleMedical($id, $data);
        return response()->json(['data' => $controle]);
    }

    public function destroy(int $id)
    {
        ControleMedicalBusiness::removeControleMedical($id);

        return response()->json(['data' => "success"]);
    }
}
