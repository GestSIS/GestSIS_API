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
            'taux_avs' => 'required|numeric|gt:0|lte:1',
            'taux_ac' => 'required|numeric|gt:0|lte:1',
            'franchise_avs' => 'required|numeric|gte:0',
            'franchise_imposition' => 'required|numeric|gte:0',
            'franchise_imposition_cantonale' => 'required|numeric|gte:0',
            'compte_id' => 'required|integer',
            'ecriture_categorie_id' => 'required|integer'
        ]);

        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
