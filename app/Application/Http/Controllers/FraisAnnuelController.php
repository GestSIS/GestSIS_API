<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class FraisAnnuelController extends Controller
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
            'montant' => 'numeric',
            'quantite' => 'numeric',
            'fonction_id' => 'exists:fonctions,id',
            'frais_annuel_type_id' => 'exists:frais_annuel_types,id',
            'type_unite_id' => 'exists:type_unites,id'
        ]);

        $frais = $this->service->ajouterFraisAnnuel($data);
        return response()->json(['data' => $frais]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'montant' => 'numeric',
            'quantite' => 'numeric',
            'fonction_id' => 'exists:fonctions,id',
            'frais_annuel_type_id' => 'exists:frais_annuel_types,id',
            'type_unite_id' => 'exists:type_unites,id'
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
