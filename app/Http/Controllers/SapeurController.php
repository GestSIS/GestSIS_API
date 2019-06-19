<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

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
        $validation = Validator::make($request->all(),
            array(
                'nom' => 'string|min:2',
                'prenom' => 'string|min:2',
                'suffixe' => 'string|nullable',
                'rue' => 'string|min:3',
                'no_rue' => 'string',
                'date_naissance' => 'date',
                'no_avs' => 'string',
                'profession' => 'string|max:80',
                'employeur' => 'string|max:150',
                'lieu_de_travail' => 'string|max:100',
                'email' => 'email',
                'actif' => 'integer',
                'iban' => 'string|max:100',
                'iban_status' => 'integer',
                'remarque' => 'string|max:300',
                'porteur' => 'boolean',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        $sapeur = SapeurBusiness::createSapeur($request->all())->getData();

        return response()->json(['data' => $sapeur]);
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
        $validation = Validator::make($request->all(),
            array(
                'nom' => 'string|min:2',
                'prenom' => 'string|min:2',
                'suffixe' => 'string|nullable',
                'rue' => 'string|min:3',
                'no_rue' => 'string',
                'date_naissance' => 'date',
                'no_avs' => 'string',
                'profession' => 'string|max:80',
                'employeur' => 'string|max:150',
                'lieu_de_travail' => 'string|max:100',
                'email' => 'email',
                'actif' => 'integer',
                'iban' => 'string|max:100',
                'iban_status' => 'integer',
                'remarque' => 'string|max:300',
                'porteur' => 'boolean',
                'localite_id' => 'integer|exists:localites,id',
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        $sapeur = SapeurBusiness::get($id)->update($request->all());

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
