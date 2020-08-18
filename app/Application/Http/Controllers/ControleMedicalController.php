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
            'sapeur_id' => 'string|min:2',
            'medecin_id' => 'string|min:2',
            'consultation' => 'string|nullable',
            'validite' => 'string|min:3',
            'en_cours' => 'string',
            'designation' => 'date|before:' . date('Y-m-d'),
            'controle_medical_type_id' => 'date',
            'accepter' => 'string'
        ]);

        $sapeur = $this->service->createControleMedical($data);

        return response()->json(['data' => $sapeur]);
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
            'sapeur_id' => 'string|min:2',
            'medecin_id' => 'string|min:2',
            'consultation' => 'string|nullable',
            'validite' => 'string|min:3',
            'en_cours' => 'string',
            'designation' => 'date|before:' . date('Y-m-d'),
            'controle_medical_type_id' => 'date',
            'accepter' => 'string'
        ]);

        $sapeur = $this->service->updateControleMedical($id, $data);

        return response()->json(['data' => $sapeur]);
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
