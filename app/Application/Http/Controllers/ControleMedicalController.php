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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $controles = $this->service->listeAllControlesMedicaux();

        return response()->json(['data' => $controles]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id)
    {
        $controle = $this->service->getControleMedical($id);
        return response()->json(['data' => $controle]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sapeur_id' => 'integer',
            'medecin_id' => 'integer',
            'controle_medical_type_id' => 'integer',
            'consultation' => 'date',
            'validite' => 'nullable|date|after:consultation',
            'designation' => 'string|nullable',
            'en_cours' => 'boolean',
            'accepter' => 'boolean'
        ]);

        $controle = $this->service->createControleMedical($data);

        return response()->json(['data' => $controle]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'id' => 'integer',
            'sapeur_id' => 'integer',
            'medecin_id' => 'integer',
            'controle_medical_type_id' => 'integer',
            'consultation' => 'date',
            'validite' => 'nullable|date|after:consultation',
            'designation' => 'string|nullable',
            'en_cours' => 'boolean',
            'accepter' => 'boolean'
        ]);

        $controle = $this->service->updateControleMedical($id, $data);
        return response()->json(['data' => $controle]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id)
    {
        $this->service->deleteControleMedical($id);

        return response()->json(['data' => "success"]);
    }
}
