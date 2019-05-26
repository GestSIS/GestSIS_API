<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     * @throws Exception
     */
    public function store(Request $request, int $id)
    {
        try {
            $mutation = SapeurBusiness::get($id)->addMutation($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
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
     * @throws Exception
     */
    public function update(Request $request, int $id, int $mutationId)
    {
        if ($mutationId !== $request->get('mutation_id')) {
            return response()->json(['error' => 'invalid mutation id']);
        }

        try {
            $mutation = SapeurBusiness::get($id)->updateMutation($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
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
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
