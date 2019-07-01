<?php

namespace App\Http\Controllers;

use App\Services\FraisService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FraisTypeController extends Controller
{
    protected $service;

    public function __construct(FraisService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $idemnites = $this->service->getFraisTypes();

        return response()->json(['data' => $idemnites]);
    }

}
