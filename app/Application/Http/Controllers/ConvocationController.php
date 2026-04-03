<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceBusiness;
use Illuminate\Http\Request;

/**
 * Controller pour la génération des convocations pdf
 * TODO: Fusionner avec ConvocationsController
 */
class ConvocationController extends Controller
{
    public function convoquer(Request $request, $exerciceComptableId)
    {
        $request->merge([
            'affichage_duree' => (bool) $request->input('affichage_duree', true),
            'affichage_pour_info' => (bool) $request->input('affichage_pour_info', false),
            'sapeurIds' => is_string($request->input('sapeurIds', '')) ? explode(',', $request->input('sapeurIds', '')) : $request->input('sapeurIds', ''),
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
        return ExerciceBusiness::convoquer($exerciceComptableId, $sapeurIds, $sisKey);
    }
}
