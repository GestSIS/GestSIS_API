<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repository\SapeurBusiness;
use App\Models\Sapeur;

class SapeurPermisController extends Controller
{

    /**
     * Return the permis
     *
     * @param int $sapeur_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $sapeur_id)
    {
        $permis = Sapeur::find($sapeur_id)->permis()->with('PermisType')->get();

        return response()->json(['data' => $permis]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(Request $request, int $id)
    {
        $permis = SapeurBusiness::get($id)->addPermis($request->all());

        //TODO Error messages
        return response()->json(['data' => $permis]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @param int $permisId
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function update(Request $request, int $id, int $permisId)
    {
        if($permisId !== $request->get('permis_id')){
            return response()->json(['error' => 'invalid permis id']);
        }

        $permis = SapeurBusiness::get($id)->updatePermis($request->all());

        //TODO Error messages
        return response()->json(['data' => $permis]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @param  int  $permisId
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id, int $permisId)
    {
        SapeurBusiness::get($id)->removePermis($permisId);

        //TODO Error messages
        return response()->json(['data' => 'success']);
    }
}
