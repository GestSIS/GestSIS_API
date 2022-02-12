<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ImputationService;
use Illuminate\Http\Request;

class ImputationController extends Controller
{
    protected $service;

    public function __construct(ImputationService $service)
    {
        $this->service = $service;
    }

    public function exercice(Request $request, int $id)
    {
        $data = $request->validate([
            'indemnite_exercice_type_id' => 'integer',
            // 'date_imputation' => 'date' // TODO: Ajouter date d'imputation ?
        ]);

        $res = $this->service->imputationExercice($id, $data);

        return response()->json(['data' => $res]);
    }

    public function cancelExercice(Request $request, int $id)
    {
        $res = $this->service->annulerImputationExercice($id);
        return response()->json(['data' => $res]);
    }

    public function intervention(Request $request, int $id)
    {
        $data = $request->validate([
            'indemnite_intervention_type_id' => 'integer',
            // 'date_imputation' => 'date' // TODO: Ajouter date imputation ?
        ]);

        $res = $this->service->imputationIntervention($id, $data);
        return response()->json(['data' => $res]);
    }

    public function cancelIntervention(Request $request, int $id)
    {
        $res = $this->service->annulerImputationIntervention($id);
        return response()->json(['data' => $res]);
    }

    public function annuel(Request $request, int $id)
    {
        $res = $this->service->imputationAnnuel($id);
        return response()->json(['data' => $res]);
    }
}
