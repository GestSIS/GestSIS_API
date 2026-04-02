<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SisParamBusiness;
use App\Infrastructure\Models\SisParam;
use Illuminate\Http\Request;

class SisParamController extends Controller
{
    public function index()
    {
        $params = SisParam::first();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'rue' => 'required|string',
            'numero' => 'required|string',
            'district' => 'required|string',
            'no_arrondissement' => 'required|string',
            'telephone' => 'required|string',
            'email' => 'required|string',
            'localite_id' => 'required|integer',
            'sapeur_id' => 'required|integer',
            'iban' => 'required|string',
            'bic' => 'required|string',
        ]);

        $params = SisParamBusiness::updateParams($data);

        return response()->json(['data' => $params]);
    }
}
