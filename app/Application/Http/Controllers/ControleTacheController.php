<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ControleBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Controle;
use App\Models\ControleTache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControleTacheController extends Controller
{
    public function index(int $controleId): JsonResponse
    {
        $taches = ControleTache::with('materielTypes')
            ->where('controle_id', $controleId)
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
            'type'        => 'required|string|in:BOOLEAN,NUMERIC,COMPOSANT',
            'obligatoire' => 'nullable|boolean',
            'materiel_type_ids'   => 'exclude_unless:type,COMPOSANT|required|array|min:1',
            'materiel_type_ids.*' => 'exclude_unless:type,COMPOSANT|integer|exists:materiel_types,id',
            'unit'        => 'nullable|string',
            'value_min'   => 'nullable|numeric',
            'value_max'   => 'nullable|numeric',
        ]);

        $this->validateNumericFields($data);

        ControleBusiness::validateTypesComposant([$data]);

        $tache = DB::transaction(function () use ($data, $controleId): ControleTache {
            $tache = ControleTache::create(['controle_id' => $controleId, ...ControleBusiness::payloadTache($data)]);
            ControleBusiness::syncTypesAcceptes($tache, $data);

            return $tache;
        });

        return response()->json(['data' => $tache->load('materielTypes')], 201);
    }

    public function update(Request $request, int $controleId, int $id): JsonResponse
    {
        $data = $request->validate([
            'nom'         => 'required|string|min:1',
            'description' => 'nullable|string',
            'order'       => 'required|integer|min:1',
            'type'        => 'required|string|in:BOOLEAN,NUMERIC,COMPOSANT',
            'obligatoire' => 'nullable|boolean',
            'materiel_type_ids'   => 'exclude_unless:type,COMPOSANT|required|array|min:1',
            'materiel_type_ids.*' => 'exclude_unless:type,COMPOSANT|integer|exists:materiel_types,id',
            'unit'        => 'nullable|string',
            'value_min'   => 'nullable|numeric',
            'value_max'   => 'nullable|numeric',
        ]);

        $this->validateNumericFields($data);

        ControleBusiness::validateTypesComposant([$data]);

        $tache = ControleTache::where('controle_id', $controleId)->findOrFail($id);

        DB::transaction(function () use ($tache, $data): void {
            $tache->update(ControleBusiness::payloadTache($data));
            ControleBusiness::syncTypesAcceptes($tache, $data);
        });

        return response()->json(['data' => $tache->load('materielTypes')]);
    }

    public function destroy(int $controleId, int $id): JsonResponse
    {
        $tache = ControleTache::where('controle_id', $controleId)->findOrFail($id);

        $tache->delete();

        return response()->json(null, 204);
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
