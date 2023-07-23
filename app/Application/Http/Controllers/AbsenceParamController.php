<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\AbsenceParamService;
use Illuminate\Http\Request;

class AbsenceParamController extends Controller
{
    protected $service;

    public function __construct(AbsenceParamService $service)
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
        ]);

        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
