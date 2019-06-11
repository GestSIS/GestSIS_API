<?php

namespace App\Http\Controllers;

use App\Repository\SapeurBusiness;
use App\Exceptions\ArrayValidatorException;
use Illuminate\Http\Request;
use App\Models\Sapeur;
use Illuminate\Http\Response;

class SapeurFonctionController extends Controller
{
 /**
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
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
