<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionParamService;
use Illuminate\Http\Request;

class TelephoneController extends Controller
{
    protected $service;

    public function __construct(InterventionParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $telephones = $this->service->telephones();

        return response()->json(['data' => $telephones]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numéro' => 'string|min:1',
            'nom' => 'strin|min:1',
            'tri' => 'integer',
        ]);

        $materiel = $this->service->ajouterTelephone($data);
        return response()->json(['data' => $materiel]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'numéro' => 'string|min:1',
            'nom' => 'strin|min:1',
            'tri' => 'integer',
        ]);

        $materiel = $this->service->modifierTelephone($id, $data);
        return response()->json(['data' => $materiel]);
    }

    public function destroy($id)
    {
        $materiel = $this->service->supprimerTelephone($id);
        return response()->json(['data' => $materiel]);
    }
}
