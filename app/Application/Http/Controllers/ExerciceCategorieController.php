<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ExerciceParamService;
use Illuminate\Http\Request;

class ExerciceCategorieController extends Controller
{
    protected $service;

    public function __construct(ExerciceParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $categorie = $this->service->categories();

        return response()->json(['data' => $categorie]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'amendable' => 'boolean|required',
            'duree_base' => 'integer|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required'
        ]);

        $categorie = $this->service->ajouterCategorie($data);
        return response()->json(['data' => $categorie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'amendable' => 'boolean|required',
            'duree_base' => 'integer|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required'
        ]);

        $categorie = $this->service->modifierCategorie($id, $data);
        return response()->json(['data' => $categorie]);
    }

    public function destroy($id)
    {
        $categorie = $this->service->supprimerCategorie($id);
        return response()->json(['data' => $categorie]);
    }
}
