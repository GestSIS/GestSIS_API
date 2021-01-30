<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteService;
use Illuminate\Http\Response;

class IndemniteTypeController extends Controller
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
