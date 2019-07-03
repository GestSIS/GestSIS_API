<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\SapeurService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class SapeurMutationController extends Controller
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
        $mutations = $this->service->getSapeurMutationsById($sapeurId);

        return response()->json(['data' => $mutations]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayValidatorException
     */
    public function store(Request $request, int $sapeurId)
    {
        $validation = Validator::make($request->all(),
            array(
                'incorporation' => 'required|date',
                'sortie' => 'date|nullable|after:incorporation',
                'motif' => 'string|nullable',
                'localite_id' => 'required|integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $mutation = $this->service->addMutation($sapeurId, $request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $mutation]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $mutationId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $sapeurId, int $mutationId)
    {
        $validation = Validator::make($request->all(),
            array(
                'id' => 'required|integer|exists:mutations,id',
                'incorporation' => 'date',
                'sortie' => 'date|nullable|after:incorporation',
                'motif' => 'string|nullable',
                'localite_id' => 'integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        if ($mutationId !== $request->get('id')) {
            return response()->json(['error' => 'invalid mutation id']);
        }

        try {
            $mutation = $this->service->updateMutation($sapeurId, $request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $mutation]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $mutationId
     * @return Response
     */
    public function destroy(int $sapeurId, int $mutationId)
    {
        try {
            $this->service->removeMutation($sapeurId, $mutationId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
