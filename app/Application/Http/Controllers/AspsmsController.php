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

    public function credit()
    {
        $credit = $this->service->credit();
        return response()->json(['data' => $credit]);
    }

    public function sendSms(Request $request)
    {
        $data = $request->validate([
            'numeros.*' => 'required|string',
            'message' => 'required|string',
            // 'origin' => 'required|string', // Pas pour le moment
            'date' => 'required|string',
        ]);

        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
