<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Controle;
use App\Models\ControleMaterielType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ControleMaterielTypeController extends Controller
{
    public function index(int $controleId): JsonResponse
    {
        $associations = ControleMaterielType::with('materielType')
            ->where('controle_id', $controleId)
            ->get();

        return response()->json(['data' => $associations]);
    }

    public function store(Request $request, int $controleId): JsonResponse
    {
        Controle::findOrFail($controleId);

        $data = $request->validate([
            'materiel_type_id' => 'required|integer|exists:materiel_types,id',
        ]);

        $this->validateTypeNonDejaAssocie($controleId, $data['materiel_type_id']);

        $association = ControleMaterielType::create(array_merge($data, ['controle_id' => $controleId]));

        return response()->json(['data' => $association->load('materielType')], 201);
    }

    public function update(Request $request, int $controleId, int $id): JsonResponse
    {
        $data = $request->validate([
            'materiel_type_id' => 'required|integer|exists:materiel_types,id',
        ]);

        $association = ControleMaterielType::where('controle_id', $controleId)->findOrFail($id);

        $this->validateTypeNonDejaAssocie($controleId, $data['materiel_type_id'], $id);

        $association->update($data);

        return response()->json(['data' => $association->load('materielType')]);
    }

    public function destroy(int $controleId, int $id): JsonResponse
    {
        $association = ControleMaterielType::where('controle_id', $controleId)->findOrFail($id);

        $association->delete();

        return response()->json(null, 204);
    }

    /**
     * Un type de matériel ne peut être rattaché qu'une fois à un contrôle donné
     * (contrainte d'unicité en base) : on le refuse avec un message métier plutôt
     * que de laisser remonter une violation d'intégrité.
     */
    private function validateTypeNonDejaAssocie(int $controleId, int $materielTypeId, ?int $exceptId = null): void
    {
        $existe = ControleMaterielType::where('controle_id', $controleId)
            ->where('materiel_type_id', $materielTypeId)
            ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();

        if ($existe) {
            throw new ArrayException([], "Ce type de matériel est déjà rattaché à ce contrôle");
        }
    }
}
