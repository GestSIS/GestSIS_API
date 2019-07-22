<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
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
        //TODO Refactor to service
        $exercice_comptable_id = $request->get('exercice_comptable_id');
        $exercices = Exercice::where('exercice_comptable_id', $exercice_comptable_id)->get();

        return response()->json(['data' => $exercices]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'date',
            'heure' => 'date_format:H:i',
            'lieu' => 'string|nullable',
            'designation' => 'string',
            'communications' => 'string|nullable',
            'duree' => 'integer|min:1|max:780',
            'status' => 'integer',
            'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
            'localite_id' => 'integer|exists:localites,id',
            'exercice_comptable_id' => 'integer|exists:exercice_comptables,id'
        ]);

        $exercice = $this->service->createExercice($data);

        return response()->json(['data' => $exercice]);
    }

    public function show($id)
    {
        $exercice = $this->service->getExerciceById($id);

        return response()->json(['data' => $exercice]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'date' => 'date',
            'heure' => 'date_format:H:i',
            'lieu' => 'string|nullable',
            'communications' => 'string|nullable',
            'designation' => 'string',
            'duree' => 'integer|min:1|max:780',
            'status' => 'integer',
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

    public function valider($id)
    {
        $exercice = $this->service->validateExercice($id);

        return response()->json(['data' => $exercice]);
    }
}
