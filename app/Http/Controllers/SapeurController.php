<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\SapeurService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class SapeurController extends Controller
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
    public function index()
    {
        return response()->json(["data" => $this->service->listeSapeurs()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(),
            array(
                'nom' => 'string|min:2',
                'prenom' => 'string|min:2',
                'suffixe' => 'string|nullable',
                'rue' => 'string|min:3',
                'no_rue' => 'string',
                'date_naissance' => 'date',
                'no_avs' => 'string',
                'profession' => 'string|max:80',
                'employeur' => 'string|max:150',
                'lieu_de_travail' => 'string|max:100',
                'email' => 'email',
                'actif' => 'integer',
                'iban' => 'string|max:100',
                'iban_status' => 'integer',
                'remarque' => 'string|max:300',
                'porteur' => 'boolean',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $sapeur = $this->service->createSapeur($validation->validated());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id)
    {
        $sapeur = $this->service->getSapeurDetailsById($id);
        return response()->json(['data' => $sapeur]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $id)
    {
        $validation = Validator::make($request->all(),
            array(
                'nom' => 'string|min:2',
                'prenom' => 'string|min:2',
                'suffixe' => 'string|nullable',
                'rue' => 'string|min:3',
                'no_rue' => 'string',
                'date_naissance' => 'date',
                'no_avs' => 'string',
                'profession' => 'string|max:80',
                'employeur' => 'string|max:150',
                'lieu_de_travail' => 'string|max:100',
                'email' => 'email',
                'actif' => 'integer',
                'iban' => 'string|max:100',
                'iban_status' => 'integer',
                'remarque' => 'string|max:300',
                'porteur' => 'boolean',
                'localite_id' => 'integer|exists:localites,id',
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }
        try {
            $sapeur = $this->service->editSapeurDetailsById($id, $validation->validated());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id)
    {
        try {
            $this->service->deleteSapeurById($id);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => "success"]);
    }
}
