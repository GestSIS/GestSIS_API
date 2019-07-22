<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteService;
use Illuminate\Http\Request;

class ImputationController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    public function exercice(Request $request, int $id)
    {
        $data = $request->validate([
            'indemnite_exercice_type_id' => 'integer'
        ]);

        $res = $this->service->imputationExercice($id, $data);

        return response()->json(['data' => $res]);
    }

    public function intervention(Request $request, int $id)
    {
        $data = $request->validate([
            'indemnite_intervention_type_id' => 'integer'
        ]);

        $res = $this->service->imputationIntervention($id, $data);
        return response()->json(['data' => $res]);
    }

    public function annuel(Request $request, int $id)
    {
        $res = $this->service->imputationAnnuel($id);
        return response()->json(['data' => $res]);
    }
}
