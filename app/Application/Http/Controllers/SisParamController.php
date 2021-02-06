<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SisParamService;
use Illuminate\Http\Request;

class SisParamController extends Controller
{
    protected $service;

    public function __construct(SisParamService $service)
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
        
        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
