<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\RtaParamBusiness;
use Illuminate\Http\Request;

class RtaParamController extends Controller
{

    public function index()
    {
        $params = RtaParamBusiness::getParams();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
        ]);

        $params = RtaParamBusiness::updateParams($data);

        return response()->json(['data' => $params]);
    }
}
