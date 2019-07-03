<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\ExerciceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class ExerciceSapeursController extends Controller
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
    public function index($exerciceId)
    {
        $sapeurs = $this->service->listSapeurOfExerciceById($exerciceId);

        return response()->json(['data' => $sapeurs]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     * @throws Exception
     */
    public function store(Request $request, int $exerciceId)
    {
        $validation = Validator::make($request->all(),
            array(
                'sapeurs.*.convoque' => 'required|boolean',
                'sapeurs.*.present' => 'required|boolean',
                'sapeurs.*.amende' => 'required|boolean',
                'sapeurs.*.remplace' => 'required|boolean',
                'sapeurs.*.excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
                'sapeurs.*.sapeur_id' => 'required|integer|exists:sapeurs,id'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $sapeur = $this->service->addSapeurs($exerciceId, $validation->validated()['sapeurs']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $exerciceId)
    {
        $validation = Validator::make($request->all(),
            array(
                'sapeurs.*.convoque' => 'required|boolean',
                'sapeurs.*.present' => 'required|boolean',
                'sapeurs.*.amende' => 'required|boolean',
                'sapeurs.*.remplace' => 'required|boolean',
                'sapeurs.*.excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $sapeur = $this->service->updateSapeurs($exerciceId, $validation->validated()['sapeurs']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     */
    public function destroy(Request $request, int $exerciceId)
    {
        $validation = Validator::make($request->all(),
            array(
                'sapeurs.*' => 'integer'
            ));

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        try {
            $this->service->removeSapeurs($exerciceId, $validation->validated()['sapeurs']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
