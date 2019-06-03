<?php

namespace App\Http\Controllers;

use App\Models\Exercice;
use App\Repository\ExerciceBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExerciceSapeurController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($exercice_id)
    {
        $sapeurs = Exercice::find($exercice_id)->sapeurs()->get();

        return response()->json(['data' => $sapeurs]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $exercice_id
     * @return Response
     * @throws Exception
     */
    public function store(Request $request, int $exercice_id)
    {
        try {
            $sapeur = ExerciceBusiness::get($exercice_id)->addSapeurs($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $exercice_id
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $exercice_id)
    {
        try {
            $sapeur = ExerciceBusiness::get($exercice_id)->updateSapeurs($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $exercice_id
     * @return Response
     */
    public function destroy(Request $request, int $exercice_id)
    {
        try {
            ExerciceBusiness::get($exercice_id)->removeSapeurs($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
