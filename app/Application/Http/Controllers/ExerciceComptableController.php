<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ExerciceComptableService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExerciceComptableController extends Controller
{

    protected $service;

    public function __construct(ExerciceComptableService $service)
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
        $exerciceComptables = $this->service->all();

        return response()->json(['data' => $exerciceComptables]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'annee' => 'required|integer',
            'designation' => 'required|string|min:1',
            'debut' => 'required|date',
            'fin' => 'required|date',
            'boucle' => 'integer'
        ]);

        $exercice = $this->service->creer($data);
        return response()->json(['data' => $exercice]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'annee' => 'integer',
            'designation' => 'string|min:1',
            'debut' => 'date',
            'fin' => 'date',
            'boucle' => 'integer',
        ]);

        $exercice = $this->service->modifier($id, $data);
        return response()->json(['data' => $exercice]);
    }

    public function destroy($id)
    {
        $exercice = $this->service->supprimer($id);
        return response()->json(['data' => $exercice]);
    }

    public function cloturer($id)
    {
        $exercice = $this->service->cloturer($id);
        return response()->json(['data' => $exercice]);
    }
}
