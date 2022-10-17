<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoParamService;
use Illuminate\Http\Request;

class MatPersoTypeController extends Controller
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
        $types = $this->service->types();

        return response()->json(['data' => $types]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'materiel_categorie_id' => 'integer|required'
        ]);

        $type = $this->service->ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'materiel_categorie_id' => 'integer|required'
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
