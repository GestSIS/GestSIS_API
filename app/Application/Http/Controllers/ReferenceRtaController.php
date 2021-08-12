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
    public function setReference(Request $request)
    {
        $data = $request->validate([
            'username' => 'string|required',
            'password' => 'string|required',
            'sis' => 'string|required',
            'communication' => 'string|required',
            'ajoutes' => 'array',
            'ajoutes.*.id' => 'required|integer',
            'ajoutes.*.nom' => 'required|string',
            'ajoutes.*.prenom' => 'required|string',
            'ajoutes.*.suffixe' => 'required|string|nullable',
            'ajoutes.*.localite' => 'required|string',
            'ajoutes.*.fonction' => 'required|string',
            'ajoutes.*.date' => 'required|date',
            'ajoutes.*.groupes' => 'required|array|min:1',
            'ajoutes.*.groupes.*.required|no' => 'integer',
            'ajoutes.*.groupes.*.required|designation' => 'string',
            'ajoutes.*.numeros' => 'required|array|min:1',
            'ajoutes.*.numeros.*' => 'required|string',
            'modifies' => 'array',
            'modifies.*.id' => 'integer|exists:reference_rtas,sapeur_id',
            'modifies.*.nom' => 'string',
            'modifies.*.prenom' => 'string',
            'modifies.*.suffixe' => 'string|nullable',
            'modifies.*.localite' => 'required|string',
            'modifies.*.fonction' => 'string',
            'modifies.*.date' => 'date',
            'modifies.*.groupes' => 'required|array|min:1',
            'modifies.*.groupes.*.no' => 'integer',
            'modifies.*.groupes.*.designation' => 'string',
            'modifies.*.numeros' => 'required|array|min:1',
            'modifies.*.numeros.*' => 'string',
            'supprimes' => 'array',
            'supprimes.*.id' => 'integer',
            'supprimes.*.nom' => 'string',
            'supprimes.*.prenom' => 'string',
            'supprimes.*.suffixe' => 'string|nullable',
            'supprimes.*.localites' => 'string|nullable',
            'supprimes.*.fonction' => 'string',
            'supprimes.*.date' => 'date',
            'supprimes.*.groupes' => 'array',
            'supprimes.*.groupes.*.no' => 'integer',
            'supprimes.*.groupes.*.designation' => 'string',
            'supprimes.*.numeros' => 'array',
            'supprimes.*.numeros.*' => 'string',
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
