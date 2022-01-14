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
            'designation' => 'string|min:1|required',
            'solde' => 'numeric|required',
            'solde_min' => 'numeric|nullable|required',
            'solde_min_pour' => 'numeric|nullable|required',
            'taux_weekend' => 'numeric|nullable|required',
            'taux_nuit' => 'numeric|nullable|required',
            'debut' => 'date_format:H:i|nullable|required',
            'fin' => 'date_format:H:i|nullable|required',
            'compte_id' => 'integer|required',
            'phase_id' => 'integer|required',
            'type_unite_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
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
            'designation' => 'string|min:1|required',
            'solde' => 'numeric|required',
            'solde_min' => 'numeric|nullable|required',
            'solde_min_pour' => 'numeric|nullable|required',
            'taux_weekend' => 'numeric|nullable|required',
            'taux_nuit' => 'numeric|nullable|required',
            'debut' => 'date_format:H:i|nullable|required',
            'fin' => 'date_format:H:i|nullable|required',
            'compte_id' => 'integer|required',
            'phase_id' => 'integer|required',
            'type_unite_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
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
