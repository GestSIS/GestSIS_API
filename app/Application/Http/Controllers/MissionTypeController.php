<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionParamService;
use Illuminate\Http\Request;

class MissionTypeController extends Controller
{
    protected $service;

    public function __construct(InterventionParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $missions = $this->service->missions();

        return response()->json(['data' => $missions]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'string|min:1'
        ]);

        $mission = $this->service->ajouterMission($data);
        return response()->json(['data' => $mission]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'titre' => 'string|min:1'
        ]);

        $mission = $this->service->modifierMission($id, $data);
        return response()->json(['data' => $mission]);
    }

    public function destroy($id)
    {
        $mission = $this->service->supprimerMission($id);
        return response()->json(['data' => $mission]);
    }
}
