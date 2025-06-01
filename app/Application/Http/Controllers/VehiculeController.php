<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionParamService;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    protected $service;

    public function __construct(InterventionParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $vehicules = $this->service->vehicules();

        return response()->json(['data' => $vehicules]);
    }
}
