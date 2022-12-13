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
            'actif' => 'required|boolean',
            'ecriture_categorie_id' => 'required|integer',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
            'fonctions.*.type_unite_id' => 'integer|required',
        ]);

        $type = $this->service->ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'actif' => 'required|boolean',
            'ecriture_categorie_id' => 'required|integer',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
            'fonctions.*.type_unite_id' => 'integer|required',
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
