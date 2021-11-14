<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class IndemniteAnnuelController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $indemnites = $this->service->indemnitesAnnuel();

        return response()->json(['data' => $indemnites]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'montant' => 'numeric',
            'quantite' => 'numeric',
            'fonction_id' => 'exists:fonctions,id',
            'indemnite_annuel_type_id' => 'exists:indemnite_annuel_types,id',
            'type_unite_id' => 'exists:type_unites,id'
        ]);

        $indemnite = $this->service->ajouterIndemniteAnnuel($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'montant' => 'numeric',
            'quantite' => 'numeric',
            'fonction_id' => 'exists:fonctions,id',
            'indemnite_annuel_type_id' => 'exists:indemnite_annuel_types,id',
            'type_unite_id' => 'exists:type_unites,id'
        ]);

        $indemnite = $this->service->modifierIndemniteAnnuel($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = $this->service->supprimerIndemniteAnnuel($id);
        return response()->json(['data' => $indemnite]);
    }
}
