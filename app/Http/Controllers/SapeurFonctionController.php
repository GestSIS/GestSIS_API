<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\SapeurService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class SapeurFonctionController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        $fonctions = $this->service->getSapeurFonctionsById($sapeurId);

        return response()->json(['data' => $fonctions]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayValidatorException
     */
    public function store(Request $request, int $sapeurId)
    {
        $validation = Validator::make($request->all(),
            array(
                'fonction_id' => 'required|integer|exists:fonctions,id',
                'debut' => 'required|date',
                'fin' => 'date|nullable|after_or_equal:debut',
                'remarque' => 'string|nullable',
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $fonction = $this->service->addFonction($sapeurId, $validation->validated());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $fonction]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $fonctionId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $sapeurId, int $fonctionId)
    {
        if ($fonctionId !== $request->get('id')) {
            return response()->json(['error' => 'invalid fonction id']);
        }

        $validation = Validator::make($request->all(),
            array(
                'id' => 'required|integer|exists:fonction_sapeur,id',
                'debut' => 'date',
                'fin' => 'date|nullable|after:debut',
                'remarque' => 'string|nullable',
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $fonction = $this->service->updateFonction($sapeurId, $validation->validated());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $fonction]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $fonctionId
     * @return Response
     */
    public function destroy(int $sapeurId, int $fonctionId)
    {
        try {
            $this->service->removeFonction($sapeurId, $fonctionId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
