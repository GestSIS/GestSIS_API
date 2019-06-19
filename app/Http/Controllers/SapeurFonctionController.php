<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurFonctionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($sapeur_id)
    {
        $fonctions = Sapeur::find($sapeur_id)->fonctions()->get();

        return response()->json(['data' => $fonctions]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayValidatorException
     */
    public function store(Request $request, int $id)
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
            $fonction = SapeurBusiness::get($id)->addFonction($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $fonction]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $fonctionId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $id, int $fonctionId)
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
            $fonction = SapeurBusiness::get($id)->updateFonction($request->all());
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $fonction]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $fonctionId
     * @return Response
     */
    public function destroy(int $id, int $fonctionId)
    {
        try {
            SapeurBusiness::get($id)->removeFonction($fonctionId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
