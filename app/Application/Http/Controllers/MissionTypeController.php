<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionParamBusiness;
use App\Models\MissionType;
use Illuminate\Http\Request;

class MissionTypeController extends Controller
{
    public function index()
    {
        $missions = MissionType::all();

        return response()->json(['data' => $missions]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'string|min:1'
        ]);

        $mission = InterventionParamBusiness::ajouterMission($data);
        return response()->json(['data' => $mission]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'titre' => 'string|min:1'
        ]);

        $mission = InterventionParamBusiness::modifierMission($id, $data);
        return response()->json(['data' => $mission]);
    }

    public function destroy($id)
    {
        InterventionParamBusiness::supprimerMission($id);
        return response()->json(['data' => 'ok']);
    }
}
