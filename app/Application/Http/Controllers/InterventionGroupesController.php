<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Infrastructure\Models\GroupeIntervention;
use Illuminate\Http\Request;

class InterventionGroupesController extends Controller
{
    protected $business;

    public function __construct(InterventionBusiness $business)
    {
        $this->business = $business;
    }

    public function index($interventionId)
    {
        return response()->json(['data' => GroupeIntervention::where('intervention_id', $interventionId)->get()]);
    }

    public function store(Request $request, int $interventionId)
    {
        $data = $request->validate([
            'groupes.*.no' => 'required|string',
            'groupes.*.designation' => 'required|string',
        ]);

        $this->business->addGroupes($interventionId, $data['groupes']);
        return response()->json(['data' => GroupeIntervention::where('intervention_id', $interventionId)->get()]);
    }

    public function destroy(Request $request, int $interventionId)
    {
        $data = $request->validate(['groupes.*' => 'required|integer']);
        $this->business->removeGroupes($interventionId, $data['groupes']);
        return response()->json(['data' => 'success']);
    }
}
