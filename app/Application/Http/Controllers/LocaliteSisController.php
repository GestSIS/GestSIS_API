<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SisParamBusiness;
use App\Models\LocaliteSis;
use Illuminate\Http\Request;

class LocaliteSisController extends Controller
{
    public function index()
    {
        $localites = LocaliteSis::pluck('localite_id')->toArray();

        return response()->json(['data' => $localites]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            '*' => 'integer|exists:localites,id|unique:localite_sis,localite_id',
        ]);

        $localites = SisParamBusiness::ajouterLocalitesSis($data);
        return response()->json(['data' => $localites]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            '*' => 'integer|exists:localite_sis,localite_id',
        ]);

        $localites = SisParamBusiness::supprimerLocalitesSis($data);
        return response()->json(['data' => $localites]);
    }
}
