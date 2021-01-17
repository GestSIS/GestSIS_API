<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteService;
use App\Infrastructure\Models\ExerciceComptable;
use Illuminate\Http\Request;

class ExerciceComptableController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteService $service)
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
        //TODO Change this to use an extra level of indirections for consistency ???
        $exerciceComptables = ExerciceComptable::all();

        return response()->json(['data' => $exerciceComptables]);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'annee' => 'integer',
            'debut'=>'date',
            'fin'=> 'date',
            'designation' => 'string',
        ]);

        $exerciceComptable = $this->service->creerExerciceComptable($data);

        return response()->json(['data' => $exerciceComptable]);
    }
}
