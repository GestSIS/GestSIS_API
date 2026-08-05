<?php

namespace App\Application\Http\Controllers;

use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class MesProchainsExercicesController extends Controller
{
    /**
     * Récupération des prochains exercices du sapeur connecté, pour tous les SIS auxquels il appartient.
     */
    public function index(Request $request)
    {
        $token = $request->attributes->get('jwtToken');
        $sapeurs = $token !== null ? (array) $token->data->sapeurs : [];

        $res = [];
        foreach ($sapeurs as $sisKey => $sapeurId) {
            Config::set('database.default', 'db_' . $sisKey);

            $convoqueParExerciceId = ExerciceSapeur::where('sapeur_id', $sapeurId)
                ->pluck('convoque', 'exercice_id');

            $exercices = Exercice::whereIn('id', $convoqueParExerciceId->keys())
                ->where('date', '>=', Carbon::now())
                ->orderBy('date')
                ->get();

            $exercices->each(function (Exercice $exercice) use ($convoqueParExerciceId) {
                $exercice->convoque = (bool) $convoqueParExerciceId->get($exercice->id);
            });

            $res[$sisKey] = $exercices;
        }

        return response()->json(['data' => $res]);
    }
}
