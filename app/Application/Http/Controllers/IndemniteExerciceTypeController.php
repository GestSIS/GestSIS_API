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
            'designation' => 'string|min:1|required',
            'type_unite_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'par_fonction' => 'boolean',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
        ]);

        $indemnite = $this->service->ajouterIndemniteExercice($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'type_unite_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'par_fonction' => 'boolean',
            'fonctions.*.type' => 'integer|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable'
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
