<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurPermisController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Return the permis
     *
     * @param int $sapeur_id
     * @return JsonResponse
     */
    public function index(int $sapeur_id)
    {
        $permis = Sapeur::find($sapeur_id)->permis()->get();

        return response()->json(['data' => $permis]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request, int $id)
    {
        $data = $request->validate([
            'permis_type_id' => 'required|integer|exists:permis_types,id',
            'date' => 'required|date|before:tomorrow'
        ]);

        $permis = $this->service->addPermis($id, $data);

        return response()->json(['data' => $permis]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param int $permisId
     * @return Response
     * @throws ArrayException
     */
    public function update(Request $request, int $id, int $permisId)
    {
        $data = $request->validate([
            'permis_id' => 'required|integer',
            'date' => 'required|date|before:tomorrow'
        ]);

        if ($permisId !== $request->get('permis_id')) {
            return response()->json(['error' => 'invalid permis id']);
        }

        $permis = $this->service->updatePermis($id, $data);

        return response()->json(['data' => $permis]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param int $permisId
     * @return Response
     */
    public function destroy(int $id, int $permisId)
    {
        $this->service->removePermis($id, $permisId);

        return response()->json(['data' => 'success']);
    }
}
