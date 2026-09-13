<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Controle;
use App\Models\ControleTache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ControleTacheController extends Controller
{
    public function index(int $controleId): JsonResponse
    {
        $taches = ControleTache::where('controle_id', $controleId)
            ->orderBy('order')
            ->get();

        return response()->json(['data' => $taches]);
    }

    public function store(Request $request, int $controleId): JsonResponse
    {
        Controle::findOrFail($controleId);

        $data = $request->validate([
            'nom'         => 'required|string|min:1',
            'description' => 'nullable|string',
            'order'       => 'required|integer|min:1',
            'type'        => 'required|string|in:BOOLEAN,NUMERIC',
            'unit'        => 'nullable|string',
            'value_min'   => 'nullable|numeric',
            'value_max'   => 'nullable|numeric',
        ]);

        $this->validateNumericFields($data);

        $tache = ControleTache::create(array_merge($data, ['controle_id' => $controleId]));

        return response()->json(['data' => $tache]);
    }

    public function update(Request $request, int $controleId, int $id): JsonResponse
    {
        $data = $request->validate([
            'nom'         => 'required|string|min:1',
            'description' => 'nullable|string',
            'order'       => 'required|integer|min:1',
            'type'        => 'required|string|in:BOOLEAN,NUMERIC',
            'unit'        => 'nullable|string',
            'value_min'   => 'nullable|numeric',
            'value_max'   => 'nullable|numeric',
        ]);

        $this->validateNumericFields($data);

        ControleTache::where('controle_id', $controleId)->whereId($id)->update($data);

        return response()->json(['data' => ControleTache::findOrFail($id)]);
    }

    public function destroy(int $controleId, int $id): JsonResponse
    {
        ControleTache::where('controle_id', $controleId)->whereId($id)->delete();

        return response()->json(['data' => true]);
    }

    /** @param array<string, mixed> $data */
    private function validateNumericFields(array $data): void
    {
        if ($data['type'] === 'BOOLEAN') {
            if (isset($data['unit']) || isset($data['value_min']) || isset($data['value_max'])) {
                throw new ArrayException([], "Les champs unit, value_min, value_max ne sont pas applicables aux tâches BOOLEAN");
            }
        }
    }
}
