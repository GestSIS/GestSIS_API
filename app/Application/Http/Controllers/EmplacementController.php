<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\EmplacementBusiness;
use Illuminate\Http\Request;

class EmplacementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $emplacements = EmplacementBusiness::listEmplacements();
        return response()->json(['data' => $emplacements]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'remarque' => 'string',
            'tri' => 'integer|required',
            'est_etiquete' => 'boolean|required',
            'impression_inventaire' => 'boolean|required',
            'couleur_id' => 'integer|min:1|required',
            'parent_id' => 'integer|nullable|required',
            'statut' => 'integer|required',
        ]);

        $emplacement = LocationBusiness::createLocation($data);
        return response()->json(['data' => $emplacement]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'remarque' => 'string',
            'tri' => 'integer|required',
            'est_etiquete' => 'boolean|required',
            'impression_inventaire' => 'boolean|required',
            'couleur_id' => 'integer|min:1|required',
            'parent_id' => 'integer|nullable|required',
            'statut' => 'integer|required',
        ]);

        $emplacement = LocationBusiness::editLocation($id, $data);
        return response()->json(['data' => $emplacement]);
    }

    public function destroy($id)
    {
        $emplacement = LocationBusiness::deleteLocation($id);
        return response()->json(['data' => $emplacement]);
    }
}
