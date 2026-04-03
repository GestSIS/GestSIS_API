<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use Illuminate\Http\Request;

class MaterielTypeArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(int $materielTypeId)
    {
        $articles = ArticleBusiness::getArticlesParMaterielType($materielTypeId);

        return response()->json(['data' => $articles]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'remarque' => 'string|nullable',
            'duree_validite' => 'integer|min:1',
            'expirable' => 'boolean',
            'tri' => 'integer'
        ]);

        $type = MaterielTypeBusiness::createProduct($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'remarque' => 'string|nullable',
            'duree_validite' => 'integer|min:1',
            'expirable' => 'boolean',
            'tri' => 'integer'
        ]);

        $type = MaterielTypeBusiness::editProduct($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = MaterielTypeBusiness::deleteProduct($id);
        return response()->json(['data' => $type]);
    }
}
