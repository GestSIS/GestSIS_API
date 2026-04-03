<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Models\Appel;
use Illuminate\Http\Request;

class InterventionAppelsController extends Controller
{
    public function index($intervention_id)
    {
        return response()->json(['data' => Appel::where('intervention_id', $intervention_id)->get()]);
    }

    public function store(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'appels.*.date' => 'required|date_format:Y-m-d H:i',
            'appels.*.numero' => 'string',
            'appels.*.nom' => 'string',
            'appels.*.commentaire' => 'string|nullable'
        ]);

        InterventionBusiness::addAppels($intervention_id, $data['appels']);
        return response()->json(['data' => Appel::where('intervention_id', $intervention_id)->get()]);
    }

    public function update(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'appels.*.id' => 'required|integer|exists:appels,id',
            'appels.*.date' => 'required|date_format:Y-m-d H:i',
            'appels.*.numero' => 'string',
            'appels.*.nom' => 'string',
            'appels.*.commentaire' => 'string|nullable'
        ]);

        InterventionBusiness::updateAppels($intervention_id, $data['appels']);
        return response()->json(['data' => Appel::where('intervention_id', $intervention_id)->get()]);
    }

    public function destroy(Request $request, int $intervention_id)
    {
        $data = $request->validate(['appels.*' => 'integer']);
        InterventionBusiness::removeAppels($intervention_id, $data['appels']);
        return response()->json(['data' => 'success']);
    }
}
