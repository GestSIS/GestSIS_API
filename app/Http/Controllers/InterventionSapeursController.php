<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Business\InterventionBusiness;
use App\Exceptions\ArrayValidatorException;
use App\Services\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionSapeursController extends Controller
{

    protected $service;

    public function __construct(InterventionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($intervention_id)
    {
        $sapeurs = $this->service->getInterventionPresences($intervention_id);

        return response()->json(['data' => $sapeurs]);
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
            $sapeurs = InterventionBusiness::get($intervention_id)->addSapeurs($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $sapeurs]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $intervention_id)
    {
        try {
            $sapeurs = InterventionBusiness::get($intervention_id)->updateSapeurs($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $sapeurs]);
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
            InterventionBusiness::get($intervention_id)->removeSapeurs($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
