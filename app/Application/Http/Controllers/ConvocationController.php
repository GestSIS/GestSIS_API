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
        $request->merge([
            'nip' => (bool) $request->get('nip', false),
            'groupe' => (bool) $request->get('groupe', false),
            'telephone' => (bool) $request->get('telephone', false),
            'adresse' => (bool) $request->get('adresse', false),
            'details' => (bool) $request->get('details', false),
            'info' => (bool) $request->get('info', false),
            'sapeurIds' => is_string($request->get('sapeurIds', '')) ? explode(',', $request->get('sapeurIds', '')) : $request->get('sapeurIds', ''),
        ]);

        $data = $request->validate([
            'nip' => 'boolean',
            'groupe' => 'boolean',
            'telephone' => 'boolean',
            'adresse' => 'boolean',
            'details' => 'boolean',
            'titre' => 'string|required',
            'info' => 'boolean',
            'pourInfo' => 'string|nullable',
            'texteDebut' => 'string|nullable',
            'texteFin' => 'string|nullable',
            'sapeurIds' => 'array|nullable',
            'sapeurIds.*' => 'integer'
        ]);
        return $this->service->convoquer($exerciceComptableId, $data);
    }
}
