<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

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
        $validation = Validator::make($request->all(),
            array(
                'sapeurs.*.debut' => 'required|date_format:Y-m-d H:i',
                'sapeurs.*.fin' => 'required|date_format:Y-m-d H:i|after:sapeurs.*.debut',
                'sapeurs.*.piquet' => 'required|boolean',
                'sapeurs.*.sapeur_id' => 'required|integer|exists:sapeurs,id'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $sapeurs = $this->service->addPresences($intervention_id, $request->get('sapeurs'));
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
        $validation = Validator::make($request->all(),
            array(
                'sapeurs.*.debut' => 'required|date_format:Y-m-d H:i',
                'sapeurs.*.fin' => 'required|date_format:Y-m-d H:i|after:sapeurs.*.debut',
                'sapeurs.*.piquet' => 'required|boolean',
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $sapeurs = $this->service->updatePresences($intervention_id, $request->get('sapeurs'));
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
        $validation = Validator::make($request->all(),
            array(
                'sapeurs.*' => 'required|integer'
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        $this->service->removePresences($intervention_id, $request->get('sapeurs'));
        return response()->json(['data' => 'success']);
    }
}
