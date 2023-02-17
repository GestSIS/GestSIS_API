<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SisParamService;
use Illuminate\Http\Request;

class ExcuseParamController extends Controller
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
            'delai_excuse' => 'required|integer',
            'email_rappel' => 'required|boolean',
            'texte_email_rappel' => 'required|string',
        ]);

        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
