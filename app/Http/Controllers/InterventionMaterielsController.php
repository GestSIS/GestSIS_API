<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Repository\InterventionBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionMaterielsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($intervention_id)
    {
        $materiels= Intervention::find($intervention_id)->materiels()->get();

        return response()->json(['data' => $materiels]);
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
            $materiels= InterventionBusiness::get($intervention_id)->addMateriels($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $materiels]);
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
            $materiels= InterventionBusiness::get($intervention_id)->updateMateriels($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $materiels]);
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
            InterventionBusiness::get($intervention_id)->removeMateriels($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
