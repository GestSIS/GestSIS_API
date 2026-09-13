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
        $dernieres = ControleExec::where('controle_id', $controleId)
            ->selectRaw('article_id, MAX(executed_at) as derniere_execution')
            ->groupBy('article_id')
            ->get()
            ->keyBy('article_id');

        return response()->json(['data' => $dernieres]);
    }

    public function store(Request $request, int $controleId, int $articleId): JsonResponse
    {
        $data = $request->validate([
            'executed_at'             => 'required|date',
            'trigger_type'            => 'required|string|in:PERIODIQUE,NON_PERIODIQUE,APRES_USAGE',
            'remarque_globale'        => 'nullable|string',
            'taches'                  => 'required|array|min:1',
            'taches.*.tache_id'       => 'required|integer',
            'taches.*.statut'         => 'nullable|string|in:OK,NOK,NA',
            'taches.*.value_measured' => 'nullable|numeric',
            'taches.*.remarque'       => 'nullable|string',
        ]);

        return response()->json(['data' => ControleExecBusiness::createExec($controleId, $articleId, $data, $request->attributes->get('sapeurId'))]);
    }
}
