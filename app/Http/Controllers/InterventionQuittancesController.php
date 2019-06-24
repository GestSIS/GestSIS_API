<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Business\InterventionBusiness;
use App\Exceptions\ArrayValidatorException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionQuittancesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($intervention_id)
    {
        $quittances = Intervention::find($intervention_id)->quittances()->get();

        return response()->json(['data' => $quittances]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     * @throws ArrayValidatorException
     */
    public function store(Request $request, int $intervention_id)
    {
        try {
            $quittances = InterventionBusiness::get($intervention_id)->addQuittances($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $quittances]);
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
            InterventionBusiness::get($intervention_id)->removeQuittances($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
