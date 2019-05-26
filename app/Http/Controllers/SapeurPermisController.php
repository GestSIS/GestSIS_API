<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurPermisController extends Controller
{

    /**
     * Return the permis
     *
     * @param int $sapeur_id
     * @return JsonResponse
     */
    public function index(int $sapeur_id)
    {
        $permis = Sapeur::find($sapeur_id)->permis()->get();

        return response()->json(['data' => $permis]);
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
            $permis = SapeurBusiness::get($id)->addPermis($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $permis]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $permisId
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $id, int $permisId)
    {
        if ($permisId !== $request->get('permis_id')) {
            return response()->json(['error' => 'invalid permis id']);
        }

        try {
            $permis = SapeurBusiness::get($id)->updatePermis($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $permis]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $permisId
     * @return Response
     */
    public function destroy(int $id, int $permisId)
    {
        try {
            SapeurBusiness::get($id)->removePermis($permisId);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
