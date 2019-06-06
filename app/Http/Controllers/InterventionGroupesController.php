<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Repository\InterventionBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionGroupesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($intervention_id)
    {
        $groupes = Intervention::find($intervention_id)->groupes()->get();

        return response()->json(['data' => $groupes]);
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
            $groupes = InterventionBusiness::get($intervention_id)->addGroupes($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $groupes]);
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
            $groupes = InterventionBusiness::get($intervention_id)->updateGroupes($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $groupes]);
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
            InterventionBusiness::get($intervention_id)->removeGroupes($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
