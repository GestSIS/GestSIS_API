<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ControleExecBusiness;
use App\Models\ControleExec;
use App\Models\ControleExecTache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ControleExecController extends Controller
{
    public function indexForArticle(int $articleId): JsonResponse
    {
        $execs = ControleExec::with(['controle', 'execTaches.article', 'executeur'])
            ->where('article_id', $articleId)
            ->orderByDesc('executed_at')
            ->get()
            ->each(function (ControleExec $exec): void {
                $exec->setAttribute('conforme', $exec->isConforme());
            });

        return response()->json(['data' => $execs]);
    }

    /**
     * Contrôles dans lesquels cet article a été référencé comme composant, du
     * plus récent au plus ancien.
     */
    public function composantsForArticle(int $articleId): JsonResponse
    {
        $references = ControleExecTache::with(['exec.controle', 'exec.article'])
            ->where('article_id', $articleId)
            ->get()
            ->sortByDesc(fn (ControleExecTache $ligne) => $ligne->exec->executed_at)
            ->values()
            ->map(fn (ControleExecTache $ligne): array => [
                'id'               => $ligne->id,
                'executed_at'      => $ligne->exec->executed_at,
                'controle_nom'     => $ligne->exec->controle->nom,
                'composant_nom'    => $ligne->nom_snapshot,
                'article_controle' => $ligne->exec->article->only(['id', 'numero', 'designation']),
            ]);

        return response()->json(['data' => $references]);
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
            'date_echeance'           => 'nullable|required_if:trigger_type,PERIODIQUE|date_format:Y-m-d',
            'taches'                  => 'nullable|array',
            'taches.*.tache_id'       => 'required|integer',
            'taches.*.statut'         => 'nullable|string|in:OK,KO,NA',
            'taches.*.value_measured' => 'nullable|numeric',
            'taches.*.article_id'     => 'nullable|integer',
            'taches.*.remarque'       => 'nullable|string',
        ]);

        return response()->json(['data' => ControleExecBusiness::createExec($controleId, $articleId, $data, $request->attributes->get('sapeurId'))], 201);
    }

    public function storeMultiple(Request $request, int $controleId): JsonResponse
    {
        $data = $request->validate([
            'executed_at'                           => 'required|date',
            'trigger_type'                           => 'required|string|in:PERIODIQUE,NON_PERIODIQUE',
            'executions'                             => 'required|array|min:1',
            'executions.*.article_id'                => 'required|integer',
            'executions.*.remarque_globale'          => 'nullable|string',
            'executions.*.date_echeance'             => 'nullable|required_if:trigger_type,PERIODIQUE|date_format:Y-m-d',
            'executions.*.taches'                    => 'nullable|array',
            'executions.*.taches.*.tache_id'         => 'required|integer',
            'executions.*.taches.*.statut'           => 'nullable|string|in:OK,KO,NA',
            'executions.*.taches.*.value_measured'   => 'nullable|numeric',
            'executions.*.taches.*.article_id'       => 'nullable|integer',
            'executions.*.taches.*.remarque'         => 'nullable|string',
        ]);

        $execs = ControleExecBusiness::createExecsMultiple(
            $controleId,
            $data['executed_at'],
            $data['trigger_type'],
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
            // Pas de trigger_type ici (fixé à la création, non modifiable) pour
            // conditionner un required_if comme sur store() : le contrôle de
            // présence pour un contrôle PERIODIQUE est fait par la couche métier
            // (ControleExecBusiness::resolveDateEcheance), qui connaît le contrôle.
            'date_echeance'           => 'nullable|date_format:Y-m-d',
            'taches'                  => 'nullable|array',
            'taches.*.id'             => 'required|integer',
            'taches.*.statut'         => 'nullable|string|in:OK,KO,NA',
            'taches.*.value_measured' => 'nullable|numeric',
            'taches.*.article_id'     => 'nullable|integer',
            'taches.*.remarque'       => 'nullable|string',
        ]);

        return response()->json(['data' => ControleExecBusiness::updateExec($id, $data)]);
    }

    public function destroy(int $id): JsonResponse
    {
        ControleExecBusiness::deleteExec($id);

        return response()->json(null, 204);
    }
}
