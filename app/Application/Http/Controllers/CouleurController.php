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
            'designation' => 'string|min:1|required',
            'parent_id' => 'integer|nullable'
        ]);

        $couleur = ColorBusiness::createColor($data);
        return response()->json(['data' => $couleur]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'parent_id' => 'integer|nullable'
        ]);

        $couleur = ColorBusiness::editColor($id, $data);
        return response()->json(['data' => $couleur]);
    }

    public function destroy($id)
    {
        $couleur = ColorBusiness::deleteColor($id);
        return response()->json(['data' => $couleur]);
    }
}
