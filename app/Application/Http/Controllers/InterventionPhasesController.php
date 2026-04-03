<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Infrastructure\Models\Phase;
use Illuminate\Http\Request;

class InterventionPhasesController extends Controller
{
    protected $business;

    public function __construct(InterventionBusiness $business)
    {
        $this->business = $business;
    }

    public function index($interventionId)
    {
        return response()->json(['data' => Phase::where('intervention_id', $interventionId)->get()]);
    }

    public function store(Request $request, int $interventionId)
    {
        $data = $request->validate([
            'phases.*.phase_type_id' => 'required|integer',
            'phases.*.debut' => 'required|date_format:Y-m-d H:i'
        ]);

        $this->business->addPhases($interventionId, $data['phases']);
        return response()->json(['data' => Phase::where('intervention_id', $interventionId)->get()]);
    }

    public function update(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'phases.*.id' => 'required|integer',
            'phases.*.phase_type_id' => 'integer',
            'phases.*.debut' => 'date_format:Y-m-d H:i|nullable'
        ]);

        $this->business->updatePhases($intervention_id, $data['phases']);
        return response()->json(['data' => Phase::where('intervention_id', $intervention_id)->get()]);
    }

    public function destroy(Request $request, int $intervention_id)
    {
        $data = $request->validate(['phases.*' => 'integer']);
        $this->business->removePhases($intervention_id, $data['phases']);
        return response()->json(['data' => 'success']);
    }
}
