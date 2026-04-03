<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Infrastructure\Models\Mission;
use Illuminate\Http\Request;

class InterventionMissionsController extends Controller
{
    protected $business;

    public function __construct(InterventionBusiness $business)
    {
        $this->business = $business;
    }

    public function index($intervention_id)
    {
        return response()->json(['data' => Mission::where('intervention_id', $intervention_id)->get()]);
    }

    public function store(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'missions.*.sapeur_id' => 'integer|exists:sapeurs,id|required_without:missions.*.sapeur',
            'missions.*.sapeur' => 'string|required_without:missions.*.sapeur_id',
            'missions.*.debut' => 'required|date_format:Y-m-d H:i',
            'missions.*.fin' => 'required|date_format:Y-m-d H:i|after:missions.*.debut',
            'missions.*.titre' => 'string',
            'missions.*.resume' => 'string|nullable'
        ]);

        $this->business->addMissions($intervention_id, $data['missions']);
        return response()->json(['data' => Mission::where('intervention_id', $intervention_id)->get()]);
    }

    public function update(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'missions.*.id' => 'integer|exists:missions,id',
            'missions.*.sapeur_id' => 'integer|exists:sapeurs,id|required_without:missions.*.sapeur',
            'missions.*.sapeur' => 'nullable|string|required_without:missions.*.sapeur_id',
            'missions.*.debut' => 'date_format:Y-m-d H:i',
            'missions.*.fin' => 'date_format:Y-m-d H:i|after:missions.*.debut',
            'missions.*.titre' => 'string',
            'missions.*.resume' => 'string|nullable'
        ]);

        $this->business->updateMissions($intervention_id, $data['missions']);
        return response()->json(['data' => Mission::where('intervention_id', $intervention_id)->get()]);
    }

    public function destroy(Request $request, int $intervention_id)
    {
        $data = $request->validate(['missions.*' => 'integer|exists:missions,id']);
        $this->business->removeMissions($intervention_id, $data['missions']);
        return response()->json(['data' => 'success']);
    }
}
