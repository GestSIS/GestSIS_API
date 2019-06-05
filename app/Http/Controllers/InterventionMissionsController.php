<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Repository\InterventionBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionMissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($intervention_id)
    {
        $missions= Intervention::find($intervention_id)->missions()->get();

        return response()->json(['data' => $missions]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     * @throws Exception
     */
    public function store(Request $request, int $intervention_id)
    {
        try {
            $missions= InterventionBusiness::get($intervention_id)->addMissions($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $missions]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $intervention_id)
    {
        try {
            $missions= InterventionBusiness::get($intervention_id)->updateMissions($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $missions]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     */
    public function destroy(Request $request, int $intervention_id)
    {
        try {
            InterventionBusiness::get($intervention_id)->removeMissions($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
