<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\CouleurBusiness;
use App\Models\Couleur;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouleurController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $couleurs = Couleur::orderBy('nom')->get();
        return response()->json(['data' => $couleurs]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required|unique:couleurs,nom',
            'texte' => 'string|required',
            'fond' => 'string|required',
            'statut' => 'boolean',
        ]);

        $couleur = CouleurBusiness::createCouleur($data);
        return response()->json(['data' => $couleur], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => ['string', 'min:1', Rule::unique('couleurs', 'nom')->ignore($id)],
            'texte' => 'string|required',
            'fond' => 'string|required',
            'statut' => 'boolean',
        ]);

        $couleur = CouleurBusiness::editCouleur($id, $data);
        return response()->json(['data' => $couleur]);
    }

    public function destroy($id)
    {
        $couleur = CouleurBusiness::deleteCouleur($id);
        return response()->json(null, 204);
    }
}
