<?php

namespace App\Domaine\API;

use App\Infrastructure\Models\Civilite;
use App\Infrastructure\Models\ConvocationParam;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceCategorie;
use App\Infrastructure\Models\Localite;
use App\Infrastructure\Models\Sapeur;

class ConvocationService
{
    public function convoquer($exerciceComptableId, array $sapeurIds)
    {
        $civilites = Civilite::all();
        $localites = Localite::all();
        $categories = ExerciceCategorie::all();
        $params = ConvocationParam::first();

        // Filtrage des personnes "pour info" 
        $exercices = Exercice::with('sapeurs')->where('exercice_comptable_id', $exerciceComptableId)->orderBy('date')->orderBy('heure')->get();
        $sapeurIds = array_values(array_unique(array_merge(...array_map(fn ($e) => array_map(fn ($c) => $c['sapeur_id'], $e['sapeurs']), $exercices->toArray()))));

        // Filtre les sapeurs à partir de $params['sapeurIds'] si existant et non vide
        if (count($sapeurIds) > 0) {
            $sapeurIds = array_intersect($sapeurIds, $sapeurIds);
        }
        $sapeurs = Sapeur::whereIn('id', $sapeurIds)->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'civilite_id', 'no_rue', 'rue', 'localite_id']);

        $civilitesMap = [];
        $localitesMap = [];
        $categoriesMap = [];
        $sapeursMap = [];
        $exercicesMap = [];
        foreach ($civilites as $e) {
            $civilitesMap[$e->id] = $e->forme_politesse;
        }
        foreach ($localites as $e) {
            $localitesMap[$e->id] = $e->npa . ' ' . $e->designation;
        }
        foreach ($categories as $e) {
            $categoriesMap[$e->id] = $e->designation;
        }
        foreach ($sapeurs as $e) {
            $sapeur = $e->toArray();
            $sapeur['exercices'] = [];
            $sapeursMap[$e->id] = $sapeur;
        }
        foreach ($exercices as $e) {
            foreach ($e['sapeurs'] as $s) {
                if (array_key_exists(strval($s->sapeur_id), $sapeursMap)) {
                    $sapeursMap[strval($s->sapeur_id)]['exercices'][] = $s->toArray();
                }
            }
            $exercicesMap[$e->id] = $e;
        }

        return View('pdf/convocation', [
            "params" => $params ?? [],
            "sapeurs" => $sapeursMap,
            "exercices" => $exercicesMap,
            "civilites" => $civilitesMap,
            "localites" => $localitesMap,
            "categories" => $categoriesMap,
        ]);
    }
}
