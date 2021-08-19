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
     * Maj de la référence RTA
     *
     * @return Response
     */
    public function setReference(Request $request)
    {
        $data = $request->validate([
            'username' => 'string|required',
            'password' => 'string|required',
            'sis' => 'string|required',
            'communication' => 'string|required',
            'ajoutes' => 'array',
            'ajoutes.*.sapeur_id' => 'required|integer',
            'ajoutes.*.nom' => 'required|string',
            'ajoutes.*.prenom' => 'required|string',
            'ajoutes.*.suffixe' => 'nullable|string',
            'ajoutes.*.localite' => 'required|string',
            'ajoutes.*.fonction' => 'nullable|string',
            'ajoutes.*.date_naissance' => 'required|date',
            'ajoutes.*.groupes' => 'required|array|min:1',
            'ajoutes.*.groupes.*.no' => 'required|integer',
            'ajoutes.*.groupes.*.designation' => 'required|string',
            'ajoutes.*.numeros' => 'required|array|min:1',
            'ajoutes.*.numeros.*' => 'required|string',
            'modifies' => 'array',
            'modifies.*.sapeur_id' => 'integer|exists:reference_rtas,sapeur_id',
            'modifies.*.nom' => 'string',
            'modifies.*.prenom' => 'string',
            'modifies.*.suffixe' => 'nullable|string',
            'modifies.*.localite' => 'required|string',
            'modifies.*.fonction' => 'nullable|string',
            'modifies.*.date_naissance' => 'date',
            'modifies.*.groupes' => 'required|array|min:1',
            'modifies.*.groupes.*.no' => 'required|integer',
            'modifies.*.groupes.*.designation' => 'required|string',
            'modifies.*.numeros' => 'required|array|min:1',
            'modifies.*.numeros.*' => 'string',
            'supprimes' => 'array',
            'supprimes.*.sapeur_id' => 'integer',
            'supprimes.*.nom' => 'string',
            'supprimes.*.prenom' => 'string',
            'supprimes.*.suffixe' => 'string|nullable',
            'supprimes.*.localites' => 'string|nullable',
            'supprimes.*.fonction' => 'string|nullable',
            'supprimes.*.date_naissance' => 'date',
        ]);

        $username = $data['username'];
        $password = $data['password'];
        $communication = $data['communication'];
        $sis = $data['sis'];

        return response()->json([
            "data" => $this->service->setReference($data, $username, $password, $communication, $sis)
        ]);
    }
}
