<?php

namespace App\Http\Controllers;

use App\Services\SapeurService;
use Illuminate\Http\Response;

class SapeurGroupeController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        $groupes = $this->service->getSapeurCoursById($sapeurId);

        return response()->json(['data' => $groupes]);
    }

}
