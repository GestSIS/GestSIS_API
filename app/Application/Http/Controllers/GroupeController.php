<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\OrganisationBusiness;
use App\Models\Groupe;
use Illuminate\Http\Request;

class GroupeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groupes = Groupe::with('sapeurIds')->get();
        return response()->json(['data' => $groupes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'no' => 'string|nullable|max:10',
            'parent_id' => 'integer|nullable',
            'tri' => 'integer',
            'type' => 'integer',
        ]);

        $groupe = OrganisationBusiness::ajouterGroupe($data);
        return response()->json(['data' => $groupe]);
    }

    public function update(Request $request, $id)
    {
        if (!Groupe::whereId($id)->exists()) {
            return response()->json(['error' => 'Groupe not found'], 404);
        }

        $data = $request->validate([
            'designation' => 'string|min:1',
            'no' => 'string|nullable|max:10',
            'parent_id' => 'integer|nullable',
            'tri' => 'integer',
            'type' => 'integer',
        ]);

        $groupe = OrganisationBusiness::modifierGroupe($id, $data);
        return response()->json(['data' => $groupe]);
    }

    public function destroy($id)
    {
        if (!Groupe::whereId($id)->exists()) {
            return response()->json(['error' => 'Groupe not found'], 404);
        }

        OrganisationBusiness::supprimerGroupe($id);
        return response()->json(['data' => 'ok']);
    }
}
