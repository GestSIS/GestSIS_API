<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\FraisIndemniteAnnuelType;
use App\Infrastructure\Models\IndemniteCoursType;
use App\Infrastructure\Models\IndemniteExerciceType;
use App\Infrastructure\Models\IndemniteInterventionType;
class FraisIndemniteTypeController extends Controller
{
    public function index()
    {
        $fraisIndemnites = [
            "annuels" => FraisIndemniteAnnuelType::with('fraisIndemniteAnnuels')->get(),
            "cours" => IndemniteCoursType::with('fonctions')->get(),
            "exercices" => IndemniteExerciceType::with('fonctions')->get(),
            "interventions" => IndemniteInterventionType::with('fonctions')->get(),
        ];

        return response()->json(['data' => $fraisIndemnites]);
    }
}
