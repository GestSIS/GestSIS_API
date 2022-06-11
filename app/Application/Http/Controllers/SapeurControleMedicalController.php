<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use Illuminate\Http\Response;

class SapeurControleMedicalController extends Controller
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
        $mutations = $this->service->getSapeurControlesMedicauxById($sapeurId);

        return response()->json(['data' => $mutations]);
    }
}
