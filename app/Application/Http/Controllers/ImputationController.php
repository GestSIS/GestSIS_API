<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ImputationBusiness;
use App\Infrastructure\Models\Ecriture;
use Illuminate\Http\Request;

class ImputationController extends Controller
{
    protected $business;

    public function __construct(ImputationBusiness $business)
    {
        $this->business = $business;
    }

    public function exercice(Request $request, int $id)
    {
        $data = $request->validate([
            'indemnite_exercice_type_id' => 'integer',
        ]);

        $statut = $this->business->imputerExercice($id, $data);
        return response()->json(['data' => [
            "statut" => $statut,
            "ecritures" => Ecriture::where('exercice_id', $id)->get(),
        ]]);
    }

    public function cancelExercice(Request $request, int $id)
    {
        $statut = $this->business->annulerImputationExercice($id);
        return response()->json(['data' => ["statut" => $statut]]);
    }

    public function intervention(Request $request, int $id)
    {
        $data = $request->validate([
            'indemnite_intervention_type_id' => 'integer',
        ]);

        $statut = $this->business->imputerIntervention($id, $data);
        return response()->json(['data' => [
            "statut" => $statut,
            "ecritures" => Ecriture::where('intervention_id', $id)->get(),
        ]]);
    }

    public function cancelIntervention(int $id)
    {
        $statut = $this->business->annulerImputationIntervention($id);
        return response()->json(['data' => ["statut" => $statut]]);
    }

    public function annuel(int $id)
    {
        $this->business->imputerAnnuel($id);
        $ecritures = Ecriture::where('exercice_comptable_id', $id)
            ->where('module', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL)
            ->get();
        return response()->json(['data' => $ecritures]);
    }

    public function cancelAnnuel(int $id)
    {
        $res = $this->business->annulerImputationAnnuel($id);
        return response()->json(['data' => $res ? 'ok' : 'ko']);
    }

    public function cours(Request $request, int $id)
    {
        $data = $request->validate([
            'indemnite_cours_type_id' => 'integer',
            'exercice_comptable_id' => 'integer',
        ]);

        $res = $this->business->imputerCours($id, $data);
        return response()->json(['data' => $res]);
    }

    public function cancelCours(int $id)
    {
        $res = $this->business->annulerImputationCours($id);
        return response()->json(['data' => $res]);
    }

    public function travail(Request $request)
    {
        $data = $request->validate([
            'ids' => 'array|required',
            'ids.*' => 'integer|required',
        ]);

        $res = $this->business->imputerTravaux($data['ids']);
        return response()->json(['data' => $res]);
    }

    public function cancelTravail($id)
    {
        $res = $this->business->annulerImputationTravail($id);
        return response()->json(['data' => $res]);
    }
}
