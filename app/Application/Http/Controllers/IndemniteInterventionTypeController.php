<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class IndemniteInterventionTypeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $indemnites = $this->service->indemnitesIntervention();

        return response()->json(['data' => $indemnites]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'solde' => 'numeric',
            'solde_min' => 'numeric|nullable',
            'solde_min_pour' => 'numeric|nullable',
            'taux_weekend' => 'numeric|nullable',
            'taux_nuit' => 'numeric|nullable',
            'debut' => 'date_format:H:i|nullable',
            'fin' => 'date_format:H:i|nullable',
            'compte_id' => 'integer',
            'type_unite_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'par_fonction' => 'boolean',
            'fonctions.*.solde' => 'numeric',
            'fonctions.*.indemnite' => 'numeric',
            'fonctions.*.fonction_id' => 'integer',
        ]);

        $indemnite = $this->service->ajouterIndemniteIntervention($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'solde' => 'numeric',
            'solde_min' => 'numeric|nullable',
            'solde_min_pour' => 'numeric|nullable',
            'taux_weekend' => 'numeric|nullable',
            'taux_nuit' => 'numeric|nullable',
            'debut' => 'date_format:H:i|nullable',
            'fin' => 'date_format:H:i|nullable',
            'compte_id' => 'integer',
            'type_unite_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'par_fonction' => 'boolean',
            'fonctions.*.id' => 'numeric|nullable',
            'fonctions.*.solde' => 'numeric',
            'fonctions.*.indemnite' => 'numeric',
            'fonctions.*.fonction_id' => 'integer',
        ]);

        $indemnite = $this->service->modifierIndemniteIntervention($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = $this->service->supprimerIndemniteIntervention($id);
        return response()->json(['data' => $indemnite]);
    }
}
