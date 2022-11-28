<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\CoursService;
use Illuminate\Http\Response;

class CoursSapeurController extends Controller
{
    public function __construct(CoursService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($exerciceComptableId)
    {
        return response()->json(['data' => $this->service->coursSapeurs($exerciceComptableId)]);
    }
}
