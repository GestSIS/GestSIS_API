<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Models\Exercice;
use App\Services\ExercicenService;
use App\Services\ExerciceService;
use Illuminate\Http\Request;
use Validator;

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
        $validation = Validator::make($request->all(),
            array(
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
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $exercice = $this->service->createExercice($validation->validated());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $exercice]);
    }

    public function show($id)
    {
        $exercice = $this->service->getExerciceById($id);

        return response()->json(['data' => $exercice]);
    }

    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(),
            array(
                'date' => 'date',
                'heure' => 'date_format:H:i',
                'lieu' => 'string|nullable',
                'communications' => 'string|nullable',
                'designation' => 'string',
                'duree' => 'integer|min:1|max:780',
                'status' => 'integer',
                'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $exercice = $this->service->updatExercice($id, $validation->validated());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $exercice]);
    }

    public function destroy($id)
    {
        try {
            $this->service->deleteExerciceById($id);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }

    public function valider($id)
    {
        try {
            $exercice = $this->service->validateExercice($id);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $exercice]);
    }
}
