<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($sapeur_id)
    {
        $grades = Sapeur::find($sapeur_id)->grades()->get();

        return response()->json(['data' => $grades]);
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
        // Ajout d'un nouveau grade
        $validation = Validator::make($request->all(),
            array(
                'grade_id' => 'required|integer|exists:grades,id',
                'date' => 'required|date',
                'remarque' => 'string|nullable',
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $grade = SapeurBusiness::get($id)->addGrade($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $grade]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $gradeId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $id, int $gradeId)
    {
        if ($gradeId !== $request->get('id')) {
            return response()->json(['error' => 'invalid grade id']);
        }

        $validation = Validator::make($request->all(),
            array(
                'date' => 'date',
                'remarque' => 'string|nullable',
                'id' => 'required|integer|exists:grade_sapeur,id'
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $grade = SapeurBusiness::get($id)->updateGrade($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $grade]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $gradeId
     * @return Response
     */
    public function destroy(int $id, int $gradeId)
    {
        try {
            SapeurBusiness::get($id)->removeGrade($gradeId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
