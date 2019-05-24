<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repository\SapeurBusiness;
use App\Models\Sapeur;

class SapeurController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //TODO: Filters
        // $availableFilters = [
        //     'actif'
        // ];
        
        // $filters = $request->only($availableFilters);
        
        $sapeurs = Sapeur::all();

        return response()->json(['data' => $sapeurs]);

        // $start = $request->input('start');
        // $limit = $request->input('limit');
        // $order = $request->input('order', '');
        // $reverseSort = $request->input('reverse', false) == '1';
        // $availableFilters = [
        //     'grp_id', 'cou_id', 'com_id', 'fon_id', 'civ_id', 'annee_inco', 'annee_sortie',
        //     'annee_naiss_de', 'annee_naiss_a', 'porteur', 'permis_c1', 'permis_b',
        //     'actif', 'politique', 'effectif_en'
        // ];

        // $sapeur = new Sapeur();

		// $sapeurs = $sapeur->getList($start, $limit, $order, $reverseSort, $request->only($availableFilters));

        // return response()->json($sapeurs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //TODO Create a new sapeur
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sapeur = Sapeur::findOrFail($id);

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        SapeurBusiness::get($id)->update($request->all());

        //TODO Error messages
        return response()->json(['data' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
