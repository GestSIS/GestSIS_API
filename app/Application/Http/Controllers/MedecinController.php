<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ControleMedicalService;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    protected $service;

    public function __construct(ControleMedicalService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $medecins = $this->service->medecins();

        return response()->json(['data' => $medecins]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'adresse' => 'string|min:1',
            'localite_id' => 'integer',
            'actif' => 'boolean'
        ]);

        $medecin = $this->service->ajouterMedecin($data);
        return response()->json(['data' => $medecin]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'adresse' => 'string|min:1',
            'localite_id' => 'integer',
            'actif' => 'boolean'
        ]);

        $medecin = $this->service->modifierMedecin($id, $data);
        return response()->json(['data' => $medecin]);
    }

    public function destroy($id)
    {
        $medecin = $this->service->supprimerMedecin($id);
        return response()->json(['data' => $medecin]);
    }
}
