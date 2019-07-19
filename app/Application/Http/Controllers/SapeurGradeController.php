<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Exceptions\ArrayException;
use App\Domaine\API\SapeurService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class SapeurGradeController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        $grades = $this->service->getSapeurGradesById($sapeurId);

        return response()->json(['data' => $grades]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request, int $sapeurId)
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
            $grade = $this->service->addGrade($sapeurId, $validation->validated());
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $grade]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $gradeId
     * @return Response
     * @throws ArrayException
     */
    public function update(Request $request, int $sapeurId, int $gradeId)
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
            $grade = $this->service->updateGrade($sapeurId, $validation->validated());
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $grade]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $gradeId
     * @return Response
     */
    public function destroy(int $sapeurId, int $gradeId)
    {
        try {
            $this->service->removeGrade($sapeurId, $gradeId);
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
