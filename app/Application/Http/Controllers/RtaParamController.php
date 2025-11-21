<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\RtaParamService;
use Illuminate\Http\Request;

class RtaParamController extends Controller
{
    protected $service;

    public function __construct(RtaParamService $service)
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
            'token' => 'required|string',
        ]);

        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
