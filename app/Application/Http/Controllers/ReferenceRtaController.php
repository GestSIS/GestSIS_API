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
    public function getReferenceRta()
    {
        return response()->json(["data" => $this->service->getReferenceRta()]);
    }

    /**
     * Get the actual gestsis version
     *
     * @return Response
     */
    public function getReferenceGestSis()
    {
        return response()->json(["data" => $this->service->getReferenceGestSis()]);
    }

    /**
     * Get the actual gestsis version
     *
     * @return Response
     */
    public function resetReferenceRta()
    {
        return response()->json(["data" => $this->service->resetReferenceRta()]);
    }

    /**
     * Maj de la référence RTA
     *
     * @return Response
     */
    public function setReference(Request $request)
    {
        $data = $request->validate([
            'sis' => 'string|required',
            'sapeurs' => 'array',
            'sapeurs.*.sapeur_id' => 'required|integer',
            'sapeurs.*.nom' => 'required|string',
            'sapeurs.*.prenom' => 'required|string',
            'sapeurs.*.suffixe' => 'nullable|string',
            'sapeurs.*.localite' => 'required|string',
            'sapeurs.*.adresse' => 'required|string',
            'sapeurs.*.fonction' => 'nullable|string',
            'sapeurs.*.date_naissance' => 'required|date',
            'sapeurs.*.groupes' => 'required|array|min:1',
            'sapeurs.*.groupes.*.no' => 'required|integer',
            'sapeurs.*.numeros' => 'required|array|min:1',
            'sapeurs.*.numeros.*' => 'required|string',
        ]);

        $sis = $data['sis'];

        return response()->json([
            "data" => $this->service->setReference($data['sapeurs'], $sis)
        ]);
    }
}
