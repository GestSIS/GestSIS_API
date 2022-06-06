<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionParamService;
use Illuminate\Http\Request;

class InterventionTraitementController extends Controller
{
    protected $service;

    public function __construct(InterventionParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $traitements = $this->service->traitements();

        return response()->json(['data' => $traitements]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer'
        ]);

        $traitement = $this->service->ajouterTraitement($data);
        return response()->json(['data' => $traitement]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer'
        ]);

        $traitement = $this->service->modifierTraitement($id, $data);
        return response()->json(['data' => $traitement]);
    }

    public function destroy($id)
    {
        $traitement = $this->service->supprimerTraitement($id);
        return response()->json(['data' => $traitement]);
    }
}
