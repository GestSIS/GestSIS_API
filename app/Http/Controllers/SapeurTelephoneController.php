<?php

namespace App\Http\Controllers;

use App\Repository\SapeurBusiness;
use Exception;
use Illuminate\Http\Request;
use App\Models\Sapeur;
use Illuminate\Http\Response;


class SapeurTelephoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param int $sapeur_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $sapeur_id)
    {
        $telephones = Sapeur::find($sapeur_id)->telephones()->get();

        return response()->json(['data' => $telephones]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function store(Request $request, int $id)
    {
        try {
            $telephone = SapeurBusiness::get($id)->addTelephone($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $telephone]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param $telephoneId
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $id, int $telephoneId)
    {
        if ($telephoneId !== $request->get('id')) {
            return response()->json(['error' => 'invalid telephone id']);
        }

        try {
            $telephone = SapeurBusiness::get($id)->updateTelephone($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $telephone]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $telephoneId
     * @return Response
     */
    public function destroy(int $id, int $telephoneId)
    {
        try {
            SapeurBusiness::get($id)->removeTelephone($telephoneId);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
