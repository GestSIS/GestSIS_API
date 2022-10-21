<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoService;

class MatPersoAlerteController extends Controller
{
    protected $service;

    public function __construct(MatPersoService $service)
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
        $alertes = $this->service->alertes();

        return response()->json(['data' => $alertes]);
    }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'titre' => 'string|min:1',
    //         'description' => 'string',
    //         'seuil_min' => 'integer',
    //         'dernier' => 'boolean',
    //         'eventTypeIds.*' => 'integer'
    //     ]);

    //     $type = $this->service->ajouterAlerteType($data);
    //     return response()->json(['data' => $type]);
    // }

    // public function update(Request $request, $id)
    // {
    //     $data = $request->validate([
    //         'titre' => 'string|min:1',
    //         'description' => 'string',
    //         'seuil_min' => 'integer',
    //         'dernier' => 'boolean',
    //         'eventTypeIds.*' => 'integer'
    //     ]);

    //     $type = $this->service->modifierAlerteType($id, $data);
    //     return response()->json(['data' => $type]);
    // }

}
