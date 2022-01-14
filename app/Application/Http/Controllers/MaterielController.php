<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionParamService;
use Illuminate\Http\Request;

class MaterielController extends Controller
{
    protected $service;

    public function __construct(InterventionParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $materiels = $this->service->materiels();

        return response()->json(['data' => $materiels]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required',
            'forfait' => 'numeric|required',
            'unite' => 'numeric|required',
            'type_unite_id' => 'integer|required'
        ]);

        $materiel = $this->service->ajouterMateriel($data);
        return response()->json(['data' => $materiel]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required',
            'forfait' => 'numeric|required',
            'unite' => 'numeric|required',
            'type_unite_id' => 'integer|required'
        ]);

        $materiel = $this->service->modifierMateriel($id, $data);
        return response()->json(['data' => $materiel]);
    }

    public function destroy($id)
    {
        $materiel = $this->service->supprimerMateriel($id);
        return response()->json(['data' => $materiel]);
    }
}
