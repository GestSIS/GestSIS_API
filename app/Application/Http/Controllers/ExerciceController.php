<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\HeureExercice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExerciceController extends Controller
{
    protected $service;

    public function __construct(ExerciceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $exerciceComptableId = $request->get('exercice_comptable_id');
        if (!$exerciceComptableId) {
            return response()->json(["Missing `exercice_comptable_id` parameter", 400]);
        }
        $exercices = $this->service->listeExercice($exerciceComptableId);
        return response()->json(['data' => $exercices]);
    }

    public function absences($exerciceComptableId)
    {
        $absences = $this->service->absences($exerciceComptableId);

        return response()->json(['data' => $absences]);
    }

    public function last()
    {
        // TODO: Refactor to service
        $threshold = Carbon::now()->subMonth()->toDateTimeString();
        $exercices = Exercice::with(['sapeurs'])->where('date', '>=', $threshold)->get()->toArray();

        $exerciceIds = array_map(fn($e) => $e['id'], $exercices);
        $heures = HeureExercice
            ::whereIn('exercice_id', $exerciceIds)
            ->get()->toArray();

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

        $exercice = $this->service->createExercice($data);

        return response()->json(['data' => $exercice]);
    }

    public function show(int $id)
    {
        $exercice = $this->service->getExerciceById($id);

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

        $exercice = $this->service->updatExercice($id, $data);

        return response()->json(['data' => $exercice]);
    }

    public function destroy($id)
    {
        $this->service->deleteExerciceById($id);

        return response()->json(['data' => 'success']);
    }

    public function annuler($id)
    {
        $statut = $this->service->cancelExerciceById($id);

        return response()->json(['data' => ['statut' => $statut]]);
    }

    public function reactiver($id)
    {
        $statut = $this->service->reactivateExerciceById($id);

        return response()->json(['data' => ['statut' => $statut]]);
    }

    public function valider($id)
    {
        $exercice = $this->service->validateExerciceById($id);

        return response()->json(['data' => $exercice]);
    }

    function listeAppel(Request $request, $exerciceId)
    {
        $sisKey = $request->header('Sis-Id', Null);
        return $this->service->listeAppel($exerciceId, $sisKey);
    }

    function listePresence(Request $request, $exerciceId)
    {
        $sisKey = $request->header('Sis-Id', Null);
        return $this->service->listePresence($exerciceId, $sisKey);
    }
}
