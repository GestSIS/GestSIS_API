<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\CouleurBusiness;
use App\Models\Couleur;
use Illuminate\Http\Request;

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
            'nom' => 'string|min:1|required',
            'texte' => 'string|required',
            'fond' => 'string|required',
        ]);

        $couleur = CouleurBusiness::createCouleur($data);
        return response()->json(['data' => $couleur]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'string|min:1',
            'texte' => 'string|required',
            'fond' => 'string|required',
        ]);

        $couleur = CouleurBusiness::editCouleur($id, $data);
        return response()->json(['data' => $couleur]);
    }

    public function destroy($id)
    {
        $couleur = CouleurBusiness::deleteCouleur($id);
        return response()->json(['data' => $couleur]);
    }
}
