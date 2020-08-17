<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ControleMedicalService;
use App\Infrastructure\Models\ControleMedical;
use App\Infrastructure\Models\Justificatif;

class ControleMedicalController extends Controller
{
    protected $service;

    public function __construct(ControleMedicalService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        //TODO extract in service
        $controles = ControleMedical::with('justificatifs')->get();

        return response()->json(['data' => $controles]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($controleId)
    {
        $controle = ControleMedical::with('justificatifs')->where('id',$controleId)->firstOrFail();
        
        return response()->json(['data' => $controle]);
    }
}
