<?php

namespace App\Domaine\API;

use Ds\Set;

use App\Infrastructure\Models\Civilite;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceCategorie;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\Localite;
use App\Infrastructure\Models\Sapeur;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Arr;

class ConvocationService
{
    public function convoquer($exerciceComptableId, $params)
    {
        $civilites = Civilite::all();
        $localites = Localite::all();
        $categories = ExerciceCategorie::all();

        $exercices = Exercice::with('sapeurs')->where('exercice_comptable_id', $exerciceComptableId)->orderBy('date')->orderBy('heure')->get();
        // $exerciceIds = array_unique(array_map(fn ($e) => $e['id'], $exercices->toArray()));
        // $convocations = ExerciceSapeur::whereIn('exercice_id', $exerciceIds)->get();
        $sapeurIds = array_values(array_unique(array_merge(...array_map(fn ($e) => array_map(fn ($c) => $c['sapeur_id'], $e['sapeurs']), $exercices->toArray()))));
        // $sapeurIds = array_unique(array_map(fn ($c) => $c['sapeur_id'], $convocations->toArray()));
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

        // return response()->json([$sapeurIds, $sapeurs]);
        // return View('pdf/convocation', [
        //     "params" => $params,
        //     "sapeurs" => $sapeursMap,
        //     "exercices" => $exercicesMap,
        //     "civilites" => $civilitesMap,
        //     "localites" => $localitesMap,
        //     "categories" => $categoriesMap,
        // ]);
        $pdf = SnappyPdf::loadView('pdf/convocation', [
            "params" => $params,
            "sapeurs" => $sapeursMap,
            "exercices" => $exercicesMap,
            "civilites" => $civilitesMap,
            "localites" => $localitesMap,
            "categories" => $categoriesMap,
        ]);
        return $pdf->download('convocations.pdf');
    }
}
