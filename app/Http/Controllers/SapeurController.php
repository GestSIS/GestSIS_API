<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $sapeurs = Sapeur::all();

        return response()->json(['data' => $sapeurs]);
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

        return response()->json(['error' => 'not implemented yet']);
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
            $sapeur = SapeurBusiness::get($id)->update($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $sapeur]);
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
