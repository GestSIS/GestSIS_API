<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Models\Intervention;
use App\Business\InterventionBusiness;
use App\Services\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionGroupesController extends Controller
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
        $groupes = $this->service->getInterventionGroupes($intervention_id);

        return response()->json(['data' => $groupes]);
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
            $groupes = InterventionBusiness::get($intervention_id)->addGroupes($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
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
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
