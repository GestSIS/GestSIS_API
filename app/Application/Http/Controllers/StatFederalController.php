<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionParamService;
use Illuminate\Http\Request;

class StatFederalController extends Controller
{

    protected $service;

    public function __construct(InterventionParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $statsFederal = $this->service->statsFederal();

        return response()->json(['data' => $statsFederal]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'statut' => 'integer',
            'tri' => 'integer'
        ]);

        $stat = $this->service->ajouterStatFederal($data);
        return response()->json(['data' => $stat]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'statut' => 'integer',
            'tri' => 'integer'
        ]);

        $stat = $this->service->modifierStatFederal($id, $data);
        return response()->json(['data' => $stat]);
    }

    public function destroy($id)
    {
        $stat = $this->service->supprimerStatFederal($id);
        return response()->json(['data' => $stat]);
    }
}
