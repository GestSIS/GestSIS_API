<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\TravauxParamService;
use Illuminate\Http\Request;

class TravailTypeController extends Controller
{

    protected $service;

    public function __construct(TravauxParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $type = $this->service->travailTypes();

        return response()->json(['data' => $type]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'tarif' => 'required|decimal',
            'type' => 'required|integer|min:1|max:',
            'type_unite_id' => 'required|integer',
            'actif' => 'required|boolean',
            'compte_id' => 'required|integer',
            'ecriture_categorie_id' => 'required|integer'
        ]);

        $type = $this->service->ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'tarif' => 'required|decimal',
            'type' => 'required|integer',
            'type_unite_id' => 'required|integer',
            'actif' => 'required|boolean',
            'compte_id' => 'required|integer',
            'ecriture_categorie_id' => 'required|integer'
        ]);

        $type = $this->service->modifierType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = $this->service->supprimerType($id);
        return response()->json(['data' => $type]);
    }
}
