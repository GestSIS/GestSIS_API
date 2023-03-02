<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ExcuseParamService;
use Illuminate\Http\Request;

class ExcuseParamController extends Controller
{
    protected $service;

    public function __construct(ExcuseParamService $service)
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
            'actif' => 'required|boolean',
            'delai_excuse' => 'required|integer',
            'email_rappel' => 'required|boolean',
            'texte_email_rappel' => 'nullable|string',
        ]);

        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
