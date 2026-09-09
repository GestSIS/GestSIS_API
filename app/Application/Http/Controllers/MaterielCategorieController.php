<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\CategorieBusiness;
use App\Models\MaterielCategorie;
use Illuminate\Http\Request;

class MaterielCategorieController extends Controller
{

    public function index()
    {
        $categories = MaterielCategorie::all();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'parent_id' => 'integer|nullable',
            'couleur_id' => 'integer',
        ]);

        $categorie = CategorieBusiness::createCategorie($data);
        return response()->json(['data' => $categorie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'parent_id' => 'integer|nullable',
            'couleur_id' => 'integer',
        ]);

        $categorie = CategorieBusiness::editCategorie($id, $data);
        return response()->json(['data' => $categorie]);
    }

    public function destroy($id)
    {
        $categorie = CategorieBusiness::deleteCategorie($id);
        return response()->json(['data' => $categorie]);
    }
}
