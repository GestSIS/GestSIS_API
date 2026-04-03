<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceBusiness;
use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\HeureExercice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExerciceController extends Controller
{

    public function index(Request $request)
    {
        $exerciceComptableId = $request->get('exercice_comptable_id');
        if (!$exerciceComptableId) {
            return response()->json(["Missing `exercice_comptable_id` parameter", 400]);
        }

        $columns = ['id', 'exercice_categorie_id', 'designation', 'date', 'heure', 'duree', 'lieu', 'communications', 'statut', 'localite_id', 'exercice_comptable_id'];
        $exercices = Exercice::where('exercice_comptable_id', $exerciceComptableId)
            ->withCount('sms')
            ->get($columns);

        return response()->json(['data' => $exercices]);
    }

    public function absences($exerciceComptableId)
    {
        $absences = ExerciceSapeur::join('exercices', 'exercices.id', '=', 'exercice_sapeur.exercice_id')
            ->join('exercice_categories', 'exercices.exercice_categorie_id', '=', 'exercice_categories.id')
            ->where('exercices.exercice_comptable_id', '=', $exerciceComptableId)
            ->where('exercices.date', '<=', Carbon::now())
            ->where('exercice_categories.amendable', '=', True)
            ->where('exercices.statut', '<>', ExerciceBusiness::EXERCICE_STATUT_ANNULE)
            ->where(function ($q) {
                $q->where('exercice_sapeur.present', '=', 0)
                    ->where('exercice_sapeur.convoque', '=', 1)
                    ->where('exercice_sapeur.remplace', '=', 0)
                    ->where('exercice_sapeur.absent', '=', 1)
                    ->orWhereNotNull('exercice_sapeur.excuse_type_id');
            })
            ->select('exercice_sapeur.*')
            ->get()->toArray();

        return response()->json(['data' => $absences]);
    }

    public function last()
    {
        $threshold = Carbon::now()->subMonth()->toDateTimeString();
        $exercices = Exercice::with(['sapeurs'])->where('date', '>=', $threshold)->get()->toArray();

        $exerciceIds = array_map(fn($e) => $e['id'], $exercices);
        $heures = HeureExercice::whereIn('exercice_id', $exerciceIds)->get()->toArray();

        $indexedExercice = [];
        foreach ($exercices as $exercice) {
            $exercice['indexedSapeurs'] = [];
            foreach ($exercice['sapeurs'] as $sapeur) {
                $exercice['indexedSapeurs'][$sapeur['sapeur_id']] = $sapeur;
                $exercice['indexedSapeurs'][$sapeur['sapeur_id']]['heures'] = [];
            }
            $indexedExercice[$exercice['id']] = $exercice;
        }

        foreach ($heures as $heure) {
            if (!array_key_exists($heure['sapeur_id'], $indexedExercice[$heure['exercice_id']]['indexedSapeurs'])) {
                $indexedExercice[$heure['exercice_id']]['indexedSapeurs'][$heure['sapeur_id']] = [
                    'convoque' => False,
                    'present' => False,
                    'absent' => False,
                    'remplace' => False,
                    'excuse_type_id' => null,
                    'heures' => [],
                ];
            }
            $indexedExercice[$heure['exercice_id']]['indexedSapeurs'][$heure['sapeur_id']]['heures'][] = $heure;
        }

        return response()->json([
            'data' => array_map(function ($e) {
                $e['sapeurs'] = array_values($e['indexedSapeurs']);
                unset($e['indexedSapeurs']);
                return $e;
            }, array_values($indexedExercice))
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'date|required',
            'heure' => 'date_format:H:i|required',
            'lieu' => 'string|nullable',
            'designation' => 'string|required',
            'communications' => 'string|nullable',
            'duree' => 'integer|min:1|max:780|required',
            'exercice_categorie_id' => 'integer|exists:exercice_categories,id|required',
            'localite_id' => 'integer|exists:localites,id|required',
            'exercice_comptable_id' => 'integer|exists:exercice_comptables,id|required'
        ]);

        $exercice = ExerciceBusiness::createExercice($data);
        return response()->json(['data' => $exercice]);
    }

    public function show(int $id)
    {
        $exercice = Exercice::with(['sapeurs'])->findOrFail($id);
        return response()->json(['data' => $exercice]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'date' => 'date',
            'heure' => 'date_format:H:i',
            'lieu' => 'string|nullable',
            'communications' => 'string|nullable',
            'designation' => 'string',
            'duree' => 'integer|min:1|max:780',
            'statut' => 'integer',
            'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
            'localite_id' => 'integer|exists:localites,id'
        ]);

        $exercice = ExerciceBusiness::updatExercice($id, $data);
        return response()->json(['data' => $exercice]);
    }

    public function destroy($id)
    {
        ExerciceBusiness::deleteExerciceById($id);
        return response()->json(['data' => 'success']);
    }

    public function annuler($id)
    {
        $statut = ExerciceBusiness::cancelExerciceById($id);
        return response()->json(['data' => ['statut' => $statut]]);
    }

    public function reactiver($id)
    {
        $statut = ExerciceBusiness::reactivateExerciceById($id);
        return response()->json(['data' => ['statut' => $statut]]);
    }

    public function valider($id)
    {
        $exercice = ExerciceBusiness::validateExerciceById($id);
        return response()->json(['data' => $exercice]);
    }

    public function listeAppel(Request $request, $exerciceId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return ExerciceBusiness::listeAppel($exerciceId, $sisKey);
    }

    public function listePresence(Request $request, $exerciceId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return ExerciceBusiness::listePresence($exerciceId, $sisKey);
    }
}
