<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\AspsmsService;
use Illuminate\Http\Request;

class AspsmsController extends Controller
{
    protected $service;

    public function __construct(AspsmsService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $credit = $this->service->index();
        return response()->json(['data' => $credit]);
    }

    public function credit()
    {
        $credit = $this->service->credit();
        return response()->json(['data' => $credit]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string',
            // 'origin' => 'required|string', // Pas pour le moment
            'differe' => 'boolean',
            'date' => 'nullable|string',
            'numeros.*' => 'required|string',
            'exerciceId' => 'nullable|integer',
        ]);

        $params = $this->service->send($data);

        return response()->json(['data' => $params]);
    }
}
