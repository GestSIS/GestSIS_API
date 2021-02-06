<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\AvsParamService;
use Illuminate\Http\Request;

class AvsParamController extends Controller
{
    protected $service;

    public function __construct(AvsParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $params = $this->service->params();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'taux_avs' => 'required|numeric',
            'taux_ac' => 'required|numeric',
            'franchise' => 'required|numeric',
            'compte_id' => 'required|integer',
        ]);
        
        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
