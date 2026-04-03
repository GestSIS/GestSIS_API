<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\CategoryBusiness;
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

        $categorie = CategoryBusiness::createCategory($data);
        return response()->json(['data' => $categorie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'parent_id' => 'integer|nullable',
            'couleur_id' => 'integer',
        ]);

        $categorie = CategoryBusiness::editCategory($id, $data);
        return response()->json(['data' => $categorie]);
    }

    public function destroy($id)
    {
        $categorie = CategoryBusiness::deleteCategory($id);
        return response()->json(['data' => $categorie]);
    }
}
