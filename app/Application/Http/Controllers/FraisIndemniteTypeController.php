<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Response;

class FraisIndemniteTypeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
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
        $fraisIndemnites = $this->service->fraisIndemnitesTypes();

        return response()->json(['data' => $fraisIndemnites]);
    }
}
