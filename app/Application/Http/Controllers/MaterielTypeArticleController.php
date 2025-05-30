<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
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

        $type = $this->service->ajouterType($data);
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

        $type = $this->service->modifierType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = $this->service->supprimerType($id);
        return response()->json(['data' => $type]);
    }
}
