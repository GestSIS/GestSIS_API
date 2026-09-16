<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ControleExecBusiness;
use App\Models\ControleExec;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ControleExecController extends Controller
{
    public function indexForArticle(int $articleId): JsonResponse
    {
        $execs = ControleExec::with(['controle', 'execTaches.tache', 'executeur'])
            ->where('article_id', $articleId)
            ->orderByDesc('executed_at')
            ->get()
            ->each(function (ControleExec $exec): void {
                $exec->setAttribute('conforme', $exec->isConforme());
            });

        return response()->json(['data' => $execs]);
    }

    public function dernieresExecutionsParArticle(int $controleId): JsonResponse
    {
        return response()->json(['data' => ControleExecBusiness::getDernieresExecutionsParArticle($controleId)]);
    }

    public function store(Request $request, int $controleId, int $articleId): JsonResponse
    {
        $data = $request->validate([
            'executed_at'             => 'required|date',
            'trigger_type'            => 'required|string|in:PERIODIQUE,NON_PERIODIQUE',
            'remarque_globale'        => 'nullable|string',
            'taches'                  => 'nullable|array',
            'taches.*.tache_id'       => 'required|integer',
            'taches.*.statut'         => 'nullable|string|in:OK,KO,NA',
            'taches.*.value_measured' => 'nullable|numeric',
            'taches.*.remarque'       => 'nullable|string',
        ]);

        return response()->json(['data' => ControleExecBusiness::createExec($controleId, $articleId, $data, $request->attributes->get('sapeurId'))]);
    }

    public function storeMultiple(Request $request, int $controleId): JsonResponse
    {
        $data = $request->validate([
            'executed_at'                           => 'required|date',
            'trigger_type'                           => 'required|string|in:PERIODIQUE,NON_PERIODIQUE',
            'remarque_globale'                       => 'nullable|string',
            'executions'                             => 'required|array|min:1',
            'executions.*.article_id'                => 'required|integer',
            'executions.*.taches'                    => 'nullable|array',
            'executions.*.taches.*.tache_id'         => 'required|integer',
            'executions.*.taches.*.statut'           => 'nullable|string|in:OK,KO,NA',
            'executions.*.taches.*.value_measured'   => 'nullable|numeric',
            'executions.*.taches.*.remarque'         => 'nullable|string',
        ]);

        $execs = ControleExecBusiness::createExecsMultiple(
            $controleId,
            $data['executed_at'],
            $data['trigger_type'],
            $data['remarque_globale'] ?? null,
            $data['executions'],
            $request->attributes->get('sapeurId'),
        );

        return response()->json(['data' => $execs]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'executed_at'             => 'required|date',
            'remarque_globale'        => 'nullable|string',
            'taches'                  => 'nullable|array',
            'taches.*.tache_id'       => 'required|integer',
            'taches.*.statut'         => 'nullable|string|in:OK,KO,NA',
            'taches.*.value_measured' => 'nullable|numeric',
            'taches.*.remarque'       => 'nullable|string',
        ]);

        return response()->json(['data' => ControleExecBusiness::updateExec($id, $data)]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['data' => ControleExecBusiness::deleteExec($id)]);
    }
}
