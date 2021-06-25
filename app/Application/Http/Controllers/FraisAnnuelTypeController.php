<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class FraisAnnuelTypeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $frais = $this->service->fraisAnnuel();

        return response()->json(['data' => $frais]);
    }

    public function store(Request $request)
    {        
        $data = $request->validate([
            'designation' => 'string|min:1',
            'montant' => 'numeric',
            'quantite' => 'numeric',
            'fonction_id' => 'integer',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'type_unite_id' => 'integer'
        ]);

        $frais = $this->service->ajouterFraisAnnuel($data);
        return response()->json(['data' => $frais]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'montant' => 'numeric',
            'quantite' => 'numeric',
            'fonction_id' => 'integer',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'type_unite_id' => 'integer'
        ]);

        $frais = $this->service->modifierFraisAnnuel($id, $data);
        return response()->json(['data' => $frais]);
    }

    public function destroy($id)
    {
        $frais = $this->service->supprimerFraisAnnuel($id);
        return response()->json(['data' => $frais]);
    }
}
