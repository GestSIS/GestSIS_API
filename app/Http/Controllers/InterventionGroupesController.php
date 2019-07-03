<?php

namespace App\Http\Controllers;

use App\Business\InterventionBusiness;
use App\Exceptions\ArrayValidatorException;
use App\Services\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

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
        $validation = Validator::make($request->all(),
            array(
                'groupes.*.groupe_id' => 'required|exists:groupes,id'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $groupes = InterventionBusiness::get($intervention_id)->addGroupes($validation->validated()['groupes']);
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
        $validation = Validator::make($request->all(),
            array(
                'groupes.*.groupe_id' => 'required|exists:groupes,id'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            InterventionBusiness::get($intervention_id)->removeGroupes($validation->validated()['groupes']);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
