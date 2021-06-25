<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionParamService;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    protected $service;

    public function __construct(InterventionParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $vehicules = $this->service->vehicules();

        return response()->json(['data' => $vehicules]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'status' => 'integer',
            'tri' => 'integer',
            'forfait' => 'numeric',
            'unite' => 'numeric',
            'type_unite_id' => 'integer'
        ]);

        $vehicule = $this->service->ajouterVehicule($data);
        return response()->json(['data' => $vehicule]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'status' => 'integer',
            'tri' => 'integer',
            'forfait' => 'numeric',
            'unite' => 'numeric',
            'type_unite_id' => 'integer'
        ]);

        $vehicule = $this->service->modifierVehicule($id, $data);
        return response()->json(['data' => $vehicule]);
    }

    public function destroy($id)
    {
        $vehicule = $this->service->supprimerVehicule($id);
        return response()->json(['data' => $vehicule]);
    }
}
