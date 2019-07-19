<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
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
        $groupes = $this->service->getSapeurGroupesById($sapeurId);

        return response()->json(['data' => $groupes]);
    }

}
