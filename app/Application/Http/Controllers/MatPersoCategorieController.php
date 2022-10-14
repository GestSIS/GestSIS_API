<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoParamService;
use Illuminate\Http\Request;

class MatPersoCategorieController extends Controller
{
    protected $service;

    public function __construct(MatPersoParamService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = $this->service->categories();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'string|min:1',
            'description' => 'string',
            'seuil_min' => 'integer',
            'dernier' => 'boolean',
            'pourEventTypes' => [
                ''
            ]
        ]);

        $categorie = $this->service->ajouterCategorie($data);
        return response()->json(['data' => $categorie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'titre' => 'string|min:1',
            'description' => 'string',
            'seuil_min' => 'integer',
            'dernier' => 'boolean'
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
