<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use App\Business\SapeurBusiness;
use App\Exceptions\ArrayValidatorException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class SapeurMutationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sapeur_id)
    {
        $mutations = Sapeur::find($sapeur_id)->mutations()->get();

        return response()->json(['data' => $mutations]);
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
        $validation = Validator::make($data,
            array(
                'incorporation' => 'required|date',
                'sortie' => 'date|nullable|after:incorporation',
                'motif' => 'string|nullable',
                'localite_id' => 'required|integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        try {
            $mutation = SapeurBusiness::get($id)->addMutation($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $mutation]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $mutationId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $id, int $mutationId)
    {
        $validation = Validator::make($data,
            array(
                'id' => 'required|integer|exists:mutations,id',
                'incorporation' => 'date',
                'sortie' => 'date|nullable|after:incorporation',
                'motif' => 'string|nullable',
                'localite_id' => 'integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        if ($mutationId !== $request->get('id')) {
            return response()->json(['error' => 'invalid mutation id']);
        }

        try {
            $mutation = SapeurBusiness::get($id)->updateMutation($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $mutation]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $mutationId
     * @return Response
     */
    public function destroy(int $id, int $mutationId)
    {
        try {
            SapeurBusiness::get($id)->removeMutation($mutationId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
