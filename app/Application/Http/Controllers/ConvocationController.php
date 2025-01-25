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
            'afficher_duree' => (bool) $request->get('afficher_duree', true),
            'afficher_pour_info' => (bool) $request->get('afficher_pour_info', false),
            'sapeurIds' => is_string($request->get('sapeurIds', '')) ? explode(',', $request->get('sapeurIds', '')) : $request->get('sapeurIds', ''),
        ]);

        $data = $request->validate([
            'titre' => 'string|required',
            'afficher_pour_info' => 'boolean',
            'afficher_duree' => 'boolean',
            'texte_pour_info' => 'string|nullable',
            'texte_debut' => 'string|nullable',
            'texte_fin' => 'string|nullable',
            'sapeurIds' => 'array|nullable',
            'sapeurIds.*' => 'integer'
        ]);
        return $this->service->convoquer($exerciceComptableId, $data);
    }
}
