<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\RtaService;
use Illuminate\Http\Request;

class ReferenceRtaController extends Controller
{
    protected $service;

    public function __construct(RtaService $service)
    {
        $this->service = $service;
    }

    /**
     * Get the actual RTA reference
     *
     * @return Response
     */
    public function getReference()
    {
        return response()->json(["data" => $this->service->getReference()]);
    }

    /**
     * Maj de la référence RTA
     *
     * @return Response
     */
    public function setReference()
    {
        return response()->json(["data" => $this->service->setReference()]);
    }
}
