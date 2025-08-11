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
            'affichage_duree' => (bool) $request->get('affichage_duree', true),
            'affichage_pour_info' => (bool) $request->get('affichage_pour_info', false),
            'sapeurIds' => is_string($request->get('sapeurIds', '')) ? explode(',', $request->get('sapeurIds', '')) : $request->get('sapeurIds', ''),
        ]);

        $sapeurIds = $request->validate([
            'sapeurIds' => 'array|nullable',
            'sapeurIds.*' => 'integer'
        ]);

        if (count($sapeurIds['sapeurIds']) === 1 && $sapeurIds['sapeurIds'][0] === "") {
            $sapeurIds['sapeurIds'] = [];
        }
        $sapeurIds = $sapeurIds['sapeurIds'] ?? [];

        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return $this->service->convoquer($exerciceComptableId, $sapeurIds, $sisKey);
    }
}
