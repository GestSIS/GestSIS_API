<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ColorBusiness;
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
        $couleurs = ColorBusiness::listColors();
        return response()->json(['data' => $couleurs]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required',
            'texte' => 'string|required',
            'fond' => 'string|required',
        ]);

        $couleur = ColorBusiness::createCouleur($data);
        return response()->json(['data' => $couleur]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'string|min:1',
            'texte' => 'string|required',
            'fond' => 'string|required',
        ]);

        $couleur = ColorBusiness::editCouleur($id, $data);
        return response()->json(['data' => $couleur]);
    }

    public function destroy($id)
    {
        $couleur = ColorBusiness::deleteCouleur($id);
        return response()->json(['data' => $couleur]);
    }
}
