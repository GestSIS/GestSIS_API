<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurTelephoneController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $sapeurId
     * @return JsonResponse
     */
    public function index(int $sapeurId)
    {
        $telephones = $this->service->getSapeurTelephonesById($sapeurId);

        return response()->json(['data' => $telephones]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request, int $sapeurId)
    {
        $data = $request->validate([
            'telephone_type_id' => 'required|integer|exists:telephone_types,id',
            'numero' => 'required|string|min:2',
            'priorite' => 'required|integer',
            'rta' => 'required|boolean',
        ]);

        $telephone = $this->service->addTelephone($sapeurId, $data);

        return response()->json(['data' => $telephone]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $telephoneId
     * @return Response
     * @throws ArrayException
     */
    public function update(Request $request, int $sapeurId, int $telephoneId)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:sapeur_telephone,id',
            'telephone_type_id' => 'integer|exists:telephone_types,id',
            'numero' => 'string|min:2',
            'priorite' => 'integer',
            'rta' => 'boolean',
        ]);

        if ($telephoneId !== $request->get('id')) {
            return response()->json(['error' => 'invalid telephone id']);
        }

        $telephone = $this->service->updateTelephone($sapeurId, $data);

        return response()->json(['data' => $telephone]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $telephoneId
     * @return Response
     */
    public function destroy(int $sapeurId, int $telephoneId)
    {
        $this->service->removeTelephone($sapeurId, $telephoneId);

        return response()->json(['data' => 'success']);
    }
}
