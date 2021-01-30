<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionParamService;
use Illuminate\Support\Facades\Request;

class StatInterventionController extends Controller
{
    protected $service;

    public function __construct(InterventionParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $stats = $this->service->stats();

        return response()->json(['data' => $stats]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer'
        ]);

        $stat = $this->service->ajouterStat($data);
        return response()->json(['data' => $stat]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer'
        ]);

        $stat = $this->service->modifierStat($id, $data);
        return response()->json(['data' => $stat]);
    }

    public function destroy($id)
    {
        $stat = $this->service->supprimerStat($id);
        return response()->json(['data' => $stat]);
    }
}
