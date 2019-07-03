<?php

namespace App\Http\Controllers;

use App\Business\SapeurBusiness;
use App\Exceptions\ArrayValidatorException;
use App\Models\Sapeur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurPermisController extends Controller
{

    /**
     * Return the permis
     *
     * @param int $sapeur_id
     * @return JsonResponse
     */
    public function index(int $sapeur_id)
    {
        $permis = Sapeur::find($sapeur_id)->permis()->get();

        return response()->json(['data' => $permis]);
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
                'permis_type_id' => 'required|integer|exists:permis_types,id',
                'date' => 'required|date|before:tomorrow',
            )
        );

        if ($validation->fails()) {
            return response()->json(["error" => $validation->errors()]);
        }

        try {
            $permis = SapeurBusiness::get($id)->addPermis($validation->validated());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $permis]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $permisId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $id, int $permisId)
    {
        $validation = Validator::make($request->all(),
            array(
                'permis_id' => 'required|integer',
                'date' => 'required|date|before:tomorrow',
            )
        );

        if ($validation->fails()) {
            return response()->json(["error" => $validation->errors()]);
        }

        if ($permisId !== $request->get('permis_id')) {
            return response()->json(['error' => 'invalid permis id']);
        }

        try {
            $permis = SapeurBusiness::get($id)->updatePermis($validation->validated());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $permis]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $permisId
     * @return Response
     */
    public function destroy(int $id, int $permisId)
    {
        try {
            SapeurBusiness::get($id)->removePermis($permisId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
