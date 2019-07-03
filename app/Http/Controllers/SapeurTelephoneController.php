<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\SapeurService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class SapeurTelephoneController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $sapeurId
     * @return JsonResponse
     */
    public function index(int $sapeurId)
    {
        $telephones = $this->service->getSapeurTelephonesById($sapeurId);

        return response()->json(['data' => $telephones]);
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
                'telephone_type_id' => 'required|integer|exists:telephone_types,id',
                'numero' => 'required|string|min:2',
                'priorite' => 'required|integer',
                'rta' => 'required|boolean',
            )
        );

        if ($validation->fails()) {
            return response()->json(["error" => $validation->errors()]);
        }

        try {
            $telephone = $this->service->addTelephone($sapeurId, $request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $telephone]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $telephoneId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $sapeurId, int $telephoneId)
    {
        $validation = Validator::make($request->all(),
            array(
                'id' => 'required|integer|exists:sapeur_telephone,id',
                'telephone_type_id' => 'integer|exists:telephone_types,id',
                'numero' => 'string|min:2',
                'priorite' => 'integer',
                'rta' => 'boolean',
            )
        );

        if ($validation->fails()) {
            return response()->json(["error" => $validation->errors()]);
        }

        if ($telephoneId !== $request->get('id')) {
            return response()->json(['error' => 'invalid telephone id']);
        }

        try {
            $telephone = $this->service->updateTelephone($sapeurId, $request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $telephone]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $telephoneId
     * @return Response
     */
    public function destroy(int $sapeurId, int $telephoneId)
    {
        try {
            $this->service->removeTelephone($sapeurId, $telephoneId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
