<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\AspsmsParamService;
use Illuminate\Http\Request;

class AspsmsParamController extends Controller
{
    protected $service;

    public function __construct(AspsmsParamService $service)
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
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
