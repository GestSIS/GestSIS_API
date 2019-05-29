<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurCoursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sapeur_id)
    {
        $cours = Sapeur::find($sapeur_id)->cours()->get();

        return response()->json(['data' => $cours]);
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
            $cours = SapeurBusiness::get($id)->addCours($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $cours]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $coursId
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $id, int $coursId)
    {
        if ($coursId !== $request->get('id')) {
            return response()->json(['error' => 'invalid cours id']);
        }

        try {
            $cours = SapeurBusiness::get($id)->updateCours($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $cours]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $coursId
     * @return Response
     */
    public function destroy(int $id, int $coursId)
    {
        try {
            SapeurBusiness::get($id)->removeCours($coursId);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => 'success']);
    }
}
