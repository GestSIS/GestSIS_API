<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExcuseParamBusiness;
use App\Models\ExcuseParam;
use Illuminate\Http\Request;

class ExcuseParamController extends Controller
{
    public function index()
    {
        $params = ExcuseParam::first();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'actif' => 'required|boolean',
            'delai_excuse' => 'required|integer',
            'email_rappel' => 'required|boolean',
            'texte_email_rappel' => 'nullable|string',
        ]);

        $params = ExcuseParamBusiness::updateParams($data);

        return response()->json(['data' => $params]);
    }
}
