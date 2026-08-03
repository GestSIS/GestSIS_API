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

            $exerciceIds = ExerciceSapeur::where('sapeur_id', $sapeurId)->pluck('exercice_id');

            $res[$sisKey] = Exercice::whereIn('id', $exerciceIds)
                ->where('date', '>=', Carbon::now())
                ->orderBy('date')
                ->get();
        }

        return response()->json(['data' => $res]);
    }
}
