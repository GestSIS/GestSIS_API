<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ControleMedicalService;
use Illuminate\Http\Request;

class ControleMedicalController extends Controller
{
    protected $service;

    public function __construct(ControleMedicalService $service)
    {
        $this->service = $service;
    }

    
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $controleId, int $id)
    {
        //TODO: Return a file
        $controle = $this->service->getJustificatif($controleId, $id);
        return response()->json(['data' => $controle]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request, int $id)
    {
        $data = $request->validate([

        ]);

        $sapeur = $this->service->addJustificatif($id, $data);

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $controleId, int $id)
    {
        $this->service->deleteJustificatif($controleId, $id);

        return response()->json(['data' => "success"]);
    }
}
