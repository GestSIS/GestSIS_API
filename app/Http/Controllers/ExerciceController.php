<?php

namespace App\Http\Controllers;

use App\Models\Exercice;
use App\Repository\ExerciceBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExerciceController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //TODO Exercice comptable filter
        $exercices = Exercice::all();

        return response()->json(['data' => $exercices]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //TODO Create a new sapeur
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $sapeur = Sapeur::findOrFail($id);

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
    public function update(Request $request, $id)
    {
        try {
            $exercice = ExerciceBusiness::get($id)->update($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $exercice]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
