<?php

namespace App\Application\Http\Controllers;

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

        $association = ControleMaterielType::create(array_merge($data, ['controle_id' => $controleId]));

        return response()->json(['data' => $association->load('materielType')]);
    }

    public function update(Request $request, int $controleId, int $id): JsonResponse
    {
        $data = $request->validate([
            'materiel_type_id' => 'required|integer|exists:materiel_types,id',
        ]);

        ControleMaterielType::where('controle_id', $controleId)->whereId($id)->update($data);

        return response()->json(['data' => ControleMaterielType::with('materielType')->findOrFail($id)]);
    }

    public function destroy(int $controleId, int $id): JsonResponse
    {
        ControleMaterielType::where('controle_id', $controleId)->whereId($id)->delete();

        return response()->json(['data' => true]);
    }
}
