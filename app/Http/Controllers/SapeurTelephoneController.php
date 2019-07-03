<?php

namespace App\Http\Controllers;

use App\Business\SapeurBusiness;
use App\Exceptions\ArrayValidatorException;
use Illuminate\Http\Request;
use App\Models\Sapeur;
use Illuminate\Http\Response;


class SapeurTelephoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param int $sapeur_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $sapeur_id)
    {
        $telephones = Sapeur::find($sapeur_id)->telephones()->get();

        return response()->json(['data' => $telephones]);
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
                'telephone_type_id' => 'required|integer|exists:telephone_types,id',
                'numero' => 'required|string|min:2',
                'priorite' => 'required|integer',
                'rta' => 'required|boolean',
            )
        );

        if ($validation->fails()) {
            return response()->json(["error"=>$validation->errors()]);
        }

        try {
            $telephone = SapeurBusiness::get($id)->addTelephone($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $telephone]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $telephoneId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $id, int $telephoneId)
    {
        $validation = Validator::make($request->all(),
            array(
                'id' => 'required|integer|exists:sapeur_telephone,id',
                'telephone_type_id' => 'integer|exists:telephone_types,id',
                'numero' => 'string|min:2',
                'priorite' => 'integer',
                'rta' => 'boolean',
            )
        );

        if ($validation->fails()) {
            return response()->json(["error"=>$validation->errors()]);
        }

        if ($telephoneId !== $request->get('id')) {
            return response()->json(['error' => 'invalid telephone id']);
        }

        try {
            $telephone = SapeurBusiness::get($id)->updateTelephone($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $telephone]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $telephoneId
     * @return Response
     */
    public function destroy(int $id, int $telephoneId)
    {
        try {
            SapeurBusiness::get($id)->removeTelephone($telephoneId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
