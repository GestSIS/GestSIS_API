<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Models\Jalon;
use Illuminate\Http\Request;

class InterventionJalonsController extends Controller
{
    public function index($intervention_id)
    {
        return response()->json(['data' => Jalon::where('intervention_id', $intervention_id)->get()]);
    }

    public function store(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'jalons.*.titre' => 'required|string',
            'jalons.*.description' => 'string|nullable',
            'jalons.*.date_time' => 'required|date_format:Y-m-d H:i',
            'jalons.*.sapeur_id' => 'nullable|integer|exists:sapeurs,id',
            'jalons.*.sapeur' => 'nullable|string'
        ]);

        InterventionBusiness::addJalons($intervention_id, $data['jalons']);
        return response()->json(['data' => Jalon::where('intervention_id', $intervention_id)->get()]);
    }

    public function update(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'jalons.*.id' => 'required|integer|exists:jalons,id',
            'jalons.*.titre' => 'required|string',
            'jalons.*.description' => 'string|nullable',
            'jalons.*.date_time' => 'required|date_format:Y-m-d H:i',
            'jalons.*.sapeur_id' => 'nullable|integer|exists:sapeurs,id',
            'jalons.*.sapeur' => 'nullable|string'
        ]);

        InterventionBusiness::updateJalons($intervention_id, $data['jalons']);
        return response()->json(['data' => Jalon::where('intervention_id', $intervention_id)->get()]);
    }

    public function destroy(Request $request, int $intervention_id)
    {
        $data = $request->validate(['jalons.*' => 'integer']);
        InterventionBusiness::removeJalons($intervention_id, $data['jalons']);
        return response()->json(['data' => 'success']);
    }
}
