<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Models\Sapeur;
use App\Business\SapeurBusiness;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class SapeurCoursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($sapeur_id)
    {
        $cours = Sapeur::find($sapeur_id)->cours()->get();

        return response()->json(['data' => $cours]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayValidatorException
     */
    public function store(Request $request, int $id)
    {
        $validation = Validator::make($request->all(),
            array(
                'date' => 'required|date',
                'localite_id' => 'integer|exists:localites,id',
                'cours_id' => 'required|integer|exists:cours,id',
                'fonction_sapeur_id' => 'integer|nullable',
                'fonction_id' => 'integer|nullable',
                'grade_id' => 'integer|nullable',
                'date_fonction' => 'bail|required_with:fonction_id|date|nullable',
                'date_grade' => 'bail|required_with:grade_id|date|nullable'
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $cours = SapeurBusiness::get($id)->addCours($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }
        return response()->json(['data' => $cours]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $coursId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $id, int $coursId)
    {
        if ($coursId !== $request->get('id')) {
            return response()->json(['error' => 'invalid cours id']);
        }

        $validation = Validator::make($request->all(),
            array(
                'id' => 'integer|exists:cours_sapeur,id',
                'date' => 'date',
                'localite_id' => 'integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $cours = SapeurBusiness::get($id)->updateCours($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }
        return response()->json(['data' => $cours]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $coursId
     * @return Response
     */
    public function destroy(int $id, int $coursId)
    {
        try {
            SapeurBusiness::get($id)->removeCours($coursId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
