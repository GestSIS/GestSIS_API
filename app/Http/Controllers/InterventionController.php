<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Repository\InterventionBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $exercice_comptable_id = $request->get('exercice_comptable_id');
        $interventions = Intervention::where('exercice_comptable_id', $exercice_comptable_id)->get();

        return response()->json(['data' => $interventions]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function store(Request $request)
    {
        try {
            $intervention = InterventionBusiness::createIntervention($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $intervention->getData()]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $intervention = Intervention::findOrFail($id);

        return response()->json(['data' => $intervention]);
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
            $intervention = InterventionBusiness::get($id)->update($request->all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $intervention]);
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
