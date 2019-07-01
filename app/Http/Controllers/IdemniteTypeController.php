<?php

namespace App\Http\Controllers;

use App\Services\ComptabiliteService;
use Illuminate\Http\Response;

class IdemniteTypeController extends Controller
{

    protected $service;

    public function __construct(ComptabiliteService $service)
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
        $idemnites = $this->service->getIndemnitesTypes();

        return response()->json(['data' => $idemnites]);
    }

}
