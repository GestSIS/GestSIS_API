<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoParamService;
use App\Domaine\Business\Materiel\ProductBusiness;
use Illuminate\Http\Request;
use Nette\NotImplementedException;

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
        throw new NotImplementedException();
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'materiel_categorie_id' => 'integer|required'
        ]);

        $type = $this->service->ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        throw new NotImplementedException();
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'materiel_categorie_id' => 'integer|required'
        ]);

        $type = $this->service->modifierType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        throw new NotImplementedException();
        $type = $this->service->supprimerType($id);
        return response()->json(['data' => $type]);
    }
}
