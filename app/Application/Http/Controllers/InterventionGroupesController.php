<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Exceptions\ArrayException;
use App\Domaine\API\InterventionService;
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
    public function index($interventionId)
    {
        $groupes = $this->service->getInterventionGroupes($interventionId);

        return response()->json(['data' => $groupes]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $interventionId
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request, int $interventionId)
    {
        $validation = Validator::make($request->all(),
            array(
                'groupes.*' => 'required|integer'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $groupes = $this->service->addGroupes($interventionId, $validation->validated()['groupes']);
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $groupes]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $interventionId
     * @return Response
     */
    public function destroy(Request $request, int $interventionId)
    {
        $validation = Validator::make($request->all(),
            array(
                'groupes.*' => 'required|integer'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $this->service->removeGroupes($interventionId, $validation->validated()['groupes']);
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
