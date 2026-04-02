<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\AspsmsBusiness;
use Illuminate\Http\Request;

class AspsmsParamController extends Controller
{
    public function index()
    {
        $params = AspsmsBusiness::getParams();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'origin' => 'string|min:1|max:11',
        ]);

        $params = AspsmsBusiness::updateParams($data);

        return response()->json(['data' => $params]);
    }
}
