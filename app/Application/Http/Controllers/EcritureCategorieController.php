<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class EcritureCategorieController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $categories = $this->service->categories();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'tri' => 'required|numeric',
        ]);

        $categorie = $this->service->ajouterCategorie($data);
        return response()->json(['data' => $categorie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'tri' => 'required|numeric',
        ]);

        $categorie = $this->service->modifierCategorie($id, $data);
        return response()->json(['data' => $categorie]);
    }

    public function destroy($id)
    {
        $this->service->supprimerCategorie($id);
        return response()->json(['data' => 'ok']);
    }
}
