<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoService;
use Illuminate\Http\Request;

class MatPersoController extends Controller
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
        $materiels = $this->service->materiels();

        return response()->json(['data' => $materiels]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function aRecuperer()
    {
        $materiels = $this->service->aRecuperer();

        return response()->json(['data' => $materiels]);
    }

}
