<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ControleBusiness;
use App\Models\Controle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ControleController extends Controller
{
    public function index(): JsonResponse
    {
        $controles = Controle::with(['taches', 'materielTypes.materielType'])->get();

        return response()->json(['data' => $controles]);
    }

    public function show(int $id): JsonResponse
    {
        $controle = Controle::with(['taches', 'materielTypes.materielType'])->findOrFail($id);

        return response()->json(['data' => $controle]);
    }

    public function statuts(): JsonResponse
    {
        return response()->json(['data' => ControleBusiness::calculerStatuts()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom'                    => 'required|string|min:1',
            'description'            => 'nullable|string',
            'recurrence_type'        => 'required|string|in:PERIODIQUE,NON_PERIODIQUE,APRES_USAGE',
            'recurrence_value'       => 'nullable|integer|min:1',
            'duree_preavis'          => 'nullable|integer|min:1',
            'externe'                => 'nullable|boolean',
            'reparateur'             => 'nullable|string',
            'taches'                 => 'nullable|array',
            'taches.*.id'            => 'nullable|integer',
            'taches.*.order'         => 'required_with:taches|integer|min:1',
            'taches.*.nom'           => 'required_with:taches|string|min:1',
            'taches.*.description'   => 'nullable|string',
            'taches.*.type'          => 'required_with:taches|string|in:BOOLEAN,NUMERIC',
            'taches.*.unit'          => 'nullable|string',
            'taches.*.value_min'     => 'nullable|numeric',
            'taches.*.value_max'     => 'nullable|numeric',
            'materiel_type_ids'      => 'nullable|array',
            'materiel_type_ids.*'    => 'integer|exists:materiel_types,id',
        ]);

        return response()->json(['data' => ControleBusiness::createControle($data)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nom'                    => 'required|string|min:1',
            'description'            => 'nullable|string',
            'recurrence_type'        => 'required|string|in:PERIODIQUE,NON_PERIODIQUE,APRES_USAGE',
            'recurrence_value'       => 'nullable|integer|min:1',
            'duree_preavis'          => 'nullable|integer|min:1',
            'externe'                => 'nullable|boolean',
            'reparateur'             => 'nullable|string',
            'taches'                 => 'nullable|array',
            'taches.*.id'            => 'nullable|integer|exists:controle_taches,id',
            'taches.*.order'         => 'required_with:taches|integer|min:1',
            'taches.*.nom'           => 'required_with:taches|string|min:1',
            'taches.*.description'   => 'nullable|string',
            'taches.*.type'          => 'required_with:taches|string|in:BOOLEAN,NUMERIC',
            'taches.*.unit'          => 'nullable|string',
            'taches.*.value_min'     => 'nullable|numeric',
            'taches.*.value_max'     => 'nullable|numeric',
            'materiel_type_ids'      => 'nullable|array',
            'materiel_type_ids.*'    => 'integer|exists:materiel_types,id',
        ]);

        return response()->json(['data' => ControleBusiness::editControle($id, $data)]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['data' => ControleBusiness::deleteControle($id)]);
    }
}
