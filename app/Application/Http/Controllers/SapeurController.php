<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return response()->json(["data" => $this->service->listeSapeurs()]);
    }

    /**
     * Return the effectif
     */
    public function effectif()
    {
        //TODO:
        return response()->json(['data' => $this->service->effectif()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:2',
            'prenom' => 'string|min:2',
            'suffixe' => 'string|nullable',
            'rue' => 'string|min:3',
            'no_rue' => 'string',
            'date_naissance' => 'date|before:' . date('Y-m-d'),
            'incorporation' => 'date|required',
            'no_avs' => 'string|nullable',
            'cotisation_avs' => 'boolean',
            'profession' => 'string|max:80|nullable',
            'employeur' => 'string|max:150|nullable',
            'lieu_de_travail' => 'string|max:100|nullable',
            'email' => 'email|nullable',
            'actif' => 'integer',
            'iban' => 'string|max:100|nullable',
            'remarque' => 'string|max:300|nullable',
            'porteur' => 'boolean',
            'localite_id' => 'integer|min:1',
            'civilite_id' => 'integer|min:1'
        ]);

        $sapeur = $this->service->createSapeur($data);

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id)
    {
        $sapeur = $this->service->getSapeurDetailsById($id);
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
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'nom' => 'string|min:2',
            'prenom' => 'string|min:2',
            'suffixe' => 'string|nullable',
            'rue' => 'string|min:3',
            'no_rue' => 'string',
            'date_naissance' => 'date|before:' . date('Y-m-d'),
            'no_avs' => 'string',
            'cotisation_avs' => 'boolean',
            'profession' => 'string|max:80|nullable',
            'employeur' => 'string|max:150|nullable',
            'lieu_de_travail' => 'string|max:100|nullable',
            'email' => 'email',
            'actif' => 'integer',
            'iban' => 'string|max:100|nullable',
            'remarque' => 'string|max:300|nullable',
            'porteur' => 'boolean|nullable',
            'localite_id' => 'integer|exists:localites,id',
            'civilite_id' => 'integer|min:1'
        ]);

        $sapeur = $this->service->editSapeurDetailsById($id, $data);

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id)
    {
        $this->service->deleteSapeurById($id);

        return response()->json(['data' => "success"]);
    }
}
