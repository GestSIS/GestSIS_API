<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Exceptions\ArrayException;
use App\Domaine\API\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class InterventionQuittancesController extends Controller
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
        $quittances = $this->service->getInterventionQuittances($intervention_id);

        return response()->json(['data' => $quittances]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request, int $intervention_id)
    {
        $validation = Validator::make($request->all(),
            array(
                'quittances.*' => 'required|integer|min:1'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $quittances = $this->service->addQuittances($intervention_id, $validation->validated()['quittances']);
        } catch (ArrayException $e) {
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
        $validation = Validator::make($request->all(),
            array(
                'quittances.*' => 'required|integer|min:1'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $this->service->removeQuittances($intervention_id, $validation->validated()['quittances']);
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
