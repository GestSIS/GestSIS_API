<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\AspsmsService;

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
}
