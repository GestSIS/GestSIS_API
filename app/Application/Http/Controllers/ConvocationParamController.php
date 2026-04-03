<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ConvocationParamBusiness;
use App\Models\ConvocationParam;
use Illuminate\Http\Request;

class ConvocationParamController extends Controller
{
    public function index()
    {
        $params = ConvocationParam::first();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'required|string|nullable',
            'texte_debut' => 'string|nullable',
            'texte_fin' => 'string|nullable',
            'texte_pour_info' => 'string|nullable',
            'affichage_duree' => 'required|bool',
            'affichage_pour_info' => 'required|bool',
        ]);

        $params = ConvocationParamBusiness::updateParams($data);

        return response()->json(['data' => $params]);
    }
}
