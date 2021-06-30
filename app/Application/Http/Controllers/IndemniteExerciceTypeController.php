<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class IndemniteExerciceTypeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $indemnites = $this->service->indemnitesExercice();

        return response()->json(['data' => $indemnites]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'solde' => 'numeric',
            'indemnite' => 'numeric',
            'solde_min' => 'numeric|nullable',
            'solde_min_pour' => 'numeric|nullable',
            'type_unite_id' => 'integer',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'par_fonction' => 'boolean',
            'fonctions.*.solde' => 'numeric',
            'fonctions.*.indemnite' => 'numeric',
            'fonctions.*.fonction_id' => 'integer',
        ]);

        $indemnite = $this->service->ajouterIndemniteExercice($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'solde' => 'numeric',
            'indemnite' => 'numeric',
            'solde_min' => 'numeric|nullable',
            'solde_min_pour' => 'numeric|nullable',
            'type_unite_id' => 'integer',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'par_fonction' => 'boolean',
            'fonctions.*.id' => 'integer|nullable',
            'fonctions.*.solde' => 'numeric',
            'fonctions.*.indemnite' => 'numeric',
            'fonctions.*.fonction_id' => 'integer'
        ]);

        $indemnite = $this->service->modifierIndemniteExercice($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = $this->service->supprimerIndemniteExercice($id);
        return response()->json(['data' => $indemnite]);
    }
}
