<?php

namespace App\Application\Http\Controllers;

use App\Models\FraisIndemniteAnnuelType;
use App\Models\IndemniteCoursType;
use App\Models\IndemniteExerciceType;
use App\Models\IndemniteInterventionType;
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
