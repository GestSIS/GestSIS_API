<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionParamBusiness;
use App\Infrastructure\Models\Materiel;
use Illuminate\Http\Request;

class MaterielController extends Controller
{
    public function index()
    {
        $materiels = Materiel::all();

        return response()->json(['data' => $materiels]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required',
            'forfait' => 'numeric|required',
            'unite' => 'numeric|required',
            'type_unite_id' => 'integer|required'
        ]);

        $materiel = InterventionParamBusiness::ajouterMateriel($data);
        return response()->json(['data' => $materiel]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required',
            'forfait' => 'numeric|required',
            'unite' => 'numeric|required',
            'type_unite_id' => 'integer|required'
        ]);

        $materiel = InterventionParamBusiness::modifierMateriel($id, $data);
        return response()->json(['data' => $materiel]);
    }

    public function destroy($id)
    {
        InterventionParamBusiness::supprimerMateriel($id);
        return response()->json(['data' => 'ok']);
    }
}
