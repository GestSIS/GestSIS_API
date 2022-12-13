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

    public function cancelIntervention(int $id)
    {
        $res = $this->service->annulerImputationIntervention($id);
        return response()->json(['data' => $res]);
    }

    public function annuel(int $id)
    {
        $res = $this->service->imputationAnnuel($id);
        return response()->json(['data' => $res]);
    }

    public function cancelAnnuel(int $id)
    {
        $res = $this->service->annulerImputationAnnuel($id);
        return response()->json(['data' => $res ? 'ok' : 'ko']);
    }

    public function cours(Request $request, int $id)
    {
        $data = $request->validate([
            'indemnite_cours_type_id' => 'integer',
            'exercice_comptable_id' => 'integer',
        ]);

        $res = $this->service->imputationCours($id, $data);
        return response()->json(['data' => $res]);
    }

    public function cancelCours(int $id)
    {
        $res = $this->service->annulerImputationCours($id);
        return response()->json(['data' => $res]);
    }

    public function travail(Request $request)
    {
        $data = $request->validate([
            'ids' => 'array|required',
            'ids.*' => 'integer|required',
        ]);

        $res = $this->service->imputationTravail($data['ids']);
        return response()->json(['data' => $res]);
    }

    public function cancelTravail(int $id)
    {
        $res = $this->service->annulerImputationTravail($id);
        return response()->json(['data' => $res]);
    }
}
