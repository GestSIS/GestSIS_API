<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\AvsParamBusiness;
use App\Infrastructure\Models\AvsParam;
use Illuminate\Http\Request;

class AvsParamController extends Controller
{
    public function index()
    {
        $params = AvsParam::first();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'taux_avs' => 'required|numeric|gte:0|lte:1',
            'taux_ac' => 'required|numeric|gte:0|lte:1',
            'franchise_avs' => 'required|numeric|gte:0',
            'franchise_imposition' => 'required|numeric|gte:0',
            'franchise_imposition_cantonale' => 'required|numeric|gte:0',
            'compte_id' => 'required|integer',
            'ecriture_categorie_id' => 'required|integer'
        ]);

        $params = AvsParamBusiness::updateParams($data);

        return response()->json(['data' => $params]);
    }
}
