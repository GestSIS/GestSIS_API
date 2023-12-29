<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoParamService;
use Illuminate\Http\Request;

class MatPersoEventTypeController extends Controller
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
        $types = $this->service->events();

        return response()->json(['data' => $types]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:1',
            'description' => 'string',
            'validable' => 'boolean',
            'materielTypeIds.*' => 'integer',
        ]);

        $type = $this->service->ajouterEventType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'string|min:1',
            'description' => 'string',
            'validable' => 'boolean',
            'materielTypeIds.*' => 'integer',
        ]);

        $type = $this->service->modifierEventType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = $this->service->supprimerEventType($id);
        return response()->json(['data' => $type]);
    }
}
