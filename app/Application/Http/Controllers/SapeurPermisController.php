<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Permis;
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
        if (!Sapeur::where('id', $sapeur_id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

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
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

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
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'id' => 'required|integer',
            'date' => 'required|date|before:tomorrow'
        ]);

        if ($permisId !== $request->get('id')) {
            return response()->json(['error' => 'invalid permis id'], 400);
        }

        // Check if permis exists
        if (!Permis::where(['id' => $permisId, 'sapeur_id' => $id])->exists()) {
            return response()->json(['error' => 'Permis non trouvé'], 404);
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
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        // Check if permis exists
        if (!Permis::where(['id' => $permisId, 'sapeur_id' => $id])->exists()) {
            return response()->json(['error' => 'Permis non trouvé'], 404);
        }

        $this->service->removePermis($id, $permisId);

        return response()->json(['data' => 'success']);
    }
}
