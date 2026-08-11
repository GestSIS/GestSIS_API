<?php

namespace App\Application\Http\Controllers;

use App\Models\ExerciceComptable;
use App\Models\ExerciceSapeur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class MesProchainesConvocationsController extends Controller
{
    /**
     * Récupération des convocations du sapeur connecté sur l'exercice comptable en cours et les
     * suivants, pour tous les SIS auxquels il appartient. Le filtre sur la date de l'exercice se
     * fait côté frontend, afin de permettre de s'excuser également sur des exercices passés si le
     * SIS l'autorise.
     */
    public function index(Request $request)
    {
        $token = $request->attributes->get('jwtToken');
        $sapeurs = $token !== null ? (array) $token->data->sapeurs : [];

        $res = [];
        foreach ($sapeurs as $sisKey => $sapeurId) {
            Config::set('database.default', 'db_' . $sisKey);

            $exerciceComptableIds = ExerciceComptable::where('fin', '>=', Carbon::now())->pluck('id');

            $res[$sisKey] = ExerciceSapeur::where('sapeur_id', $sapeurId)
                ->whereHas('exercice', function ($query) use ($exerciceComptableIds) {
                    $query->whereIn('exercice_comptable_id', $exerciceComptableIds);
                })
                ->with(['exercice', 'exercice.categorie'])
                ->get()
                ->sortBy('exercice.date')
                ->values()
                ->map(function (ExerciceSapeur $exerciceSapeur) {
                    $exercice = $exerciceSapeur->exercice;
                    $exercice->presence = $exerciceSapeur->toArray();

                    return $exercice;
                });
        }

        return response()->json(['data' => $res]);
    }
}
