<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoParamService;
use App\Domaine\Business\Materiel\ProductBusiness;
use Illuminate\Http\Request;

class MaterielTypeController extends Controller
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
        $types = ProductBusiness::listProductsBasicByCategory();
        return response()->json(['data' => $types]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'materiel_categorie_id' => 'integer|required',
            'est_numerote' => 'boolean|required',
            'est_attribuable' => 'boolean|required',
            'est_taillee' => 'boolean|required',
            'prix' => 'string|nullable',
            'fournisseur' => 'string|nullable',
            'reparateur' => 'string|nullable',
            // 'a_controller' => 'string',
            'remarque' => 'string|nullable',
            'prefix' => 'string|nullable',
        ]);

        $type = ProductBusiness::createProduct($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'materiel_categorie_id' => 'integer|required',
            'est_numerote' => 'boolean|required',
            'est_attribuable' => 'boolean|required',
            'est_taillee' => 'boolean|required',
            'prix' => 'string|nullable',
            'fournisseur' => 'string|nullable',
            'reparateur' => 'string|nullable',
            // 'a_controller' => 'string',
            'remarque' => 'string|nullable',
            'prefix' => 'string|nullable',
        ]);

        $type = ProductBusiness::editProduct($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = ProductBusiness::deleteProduct($id);
        return response()->json(['data' => $type]);
    }
}
