<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Models\Exercice;
use App\Services\ExercicenService;
use App\Services\ExerciceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class ExerciceController extends Controller
{

    protected $service;

    public function __construct(ExerciceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //TODO Refactor to service
        $exercice_comptable_id = $request->get('exercice_comptable_id');
        $exercices = Exercice::where('exercice_comptable_id', $exercice_comptable_id)->get();

        return response()->json(['data' => $exercices]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
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

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $exercice = $this->service->getExerciceById($id);

        return response()->json(['data' => $exercice]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws Exception
     */
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

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $exercice = $this->service->deleteExerciceById($id);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
