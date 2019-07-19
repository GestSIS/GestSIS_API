<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteService;
use Illuminate\Http\Response;

class FraisTypeController extends Controller
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
        $idemnites = $this->service->getFraisTypes();

        return response()->json(['data' => $idemnites]);
    }

}
