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
            'titre' => 'string|min:1',
            'description' => 'string',
            'seuil_min' => 'integer',
            'dernier' => 'boolean',
            'pourEventTypes' => [
                ''
            ]
        ]);

        $type = $this->service->ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'titre' => 'string|min:1',
            'description' => 'string',
            'seuil_min' => 'integer',
            'dernier' => 'boolean'
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
