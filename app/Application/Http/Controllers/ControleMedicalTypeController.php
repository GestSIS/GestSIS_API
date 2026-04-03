<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ControleMedicalBusiness;
use App\Infrastructure\Models\ControleMedicalType;
use Illuminate\Http\Request;

class ControleMedicalTypeController extends Controller
{
    public function index()
    {
        return response()->json(['data' => ControleMedicalType::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'remarque' => 'string|nullable',
            'duree_validite' => 'integer|min:1',
            'expirable' => 'boolean',
            'tri' => 'integer'
        ]);

        $type = ControleMedicalBusiness::ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'remarque' => 'string|nullable',
            'duree_validite' => 'integer|min:1',
            'expirable' => 'boolean',
            'tri' => 'integer'
        ]);

        $type = ControleMedicalBusiness::modifierType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = ControleMedicalBusiness::supprimerType($id);
        return response()->json(['data' => $type]);
    }
}
