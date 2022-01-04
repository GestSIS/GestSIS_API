<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ConvocationService;
use Illuminate\Http\Request;

/**
 * Controller pour la génération des convocations pdf
 * TODO: Fusionner avec ConvocationsController
 */
class ConvocationController extends Controller
{
    protected $service;

    public function __construct(ConvocationService $service)
    {
        $this->service = $service;
    }

    public function convoquer(Request $request, $exerciceComptableId)
    {
        $data = $request->validate([
            'nip' => 'boolean',
            'groupe' => 'boolean',
            'telephone' => 'boolean',
            'adresse' => 'boolean',
            'details' => 'boolean',
            'format' => 'integer|required',
            'titre' => 'string|required',
            'info' => 'boolean',
            'pourInfo' => 'string|nullable',
            'texteDebut' => 'string|nullable',
            'texteFin' => 'string|nullable',
        ]);

        return $this->service->convoquer($exerciceComptableId, $data);
    }
}
