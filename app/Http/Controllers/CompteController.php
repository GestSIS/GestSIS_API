<?php

namespace App\Http\Controllers;

use App\Services\ComptabiliteService;
use Illuminate\Http\Response;

class CompteController extends Controller
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
        return response()->json(['data' => $this->service->getComptes()]);
    }
}
