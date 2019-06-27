<?php

namespace App\Http\Controllers;

use App\Services\FraisService;
use Illuminate\Http\Request;

class ImputationController extends Controller
{
    protected $service;

    public function __construct(FraisService $service)
    {
        $this->service = $service;
    }

    public function exercice(Request $request, int $id)
    {
        $temp = $this->service->generateExercice($id, $request->all());

        return response()->json(['data' => $temp]);
    }

    public function intervention(Request $request, int $id)
    {
        $temp = $this->service->generateIntervention($id, $request->all());

        return response()->json(['data' => $temp]);
    }

    public function indemniteAnnuel()
    {

        return response()->json(['data' => 'TODO']);
    }

    public function fraisAnnuel()
    {


        return response()->json(['data' => 'TODO']);
    }
}
