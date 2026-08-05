<?php

namespace App\Application\Http\Controllers;

use App\Models\ExerciceSapeur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class MesProchainesConvocationsController extends Controller
{
    /**
     * Récupération des prochaines convocations du sapeur connecté, pour tous les SIS auxquels il appartient.
     */
    public function index(Request $request)
    {
        $token = $request->attributes->get('jwtToken');
        $sapeurs = $token !== null ? (array) $token->data->sapeurs : [];

        $res = [];
        foreach ($sapeurs as $sisKey => $sapeurId) {
            Config::set('database.default', 'db_' . $sisKey);

            $res[$sisKey] = ExerciceSapeur::where('sapeur_id', $sapeurId)
                ->whereHas('exercice', function ($query) {
                    $query->where('date', '>=', Carbon::now());
                })
                ->with('exercice')
                ->get()
                ->sortBy('exercice.date')
                ->values()
                ->map(function (ExerciceSapeur $exerciceSapeur) {
                    $exercice = $exerciceSapeur->exercice;
                    $exercice->convoque = (bool) $exerciceSapeur->convoque;

                    return $exercice;
                });
        }

        return response()->json(['data' => $res]);
    }
}
