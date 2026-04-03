<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceBusiness;
use Illuminate\Http\Request;

class HeureExerciceController extends Controller
{
    protected $business;

    public function __construct(ExerciceBusiness $business)
    {
        $this->business = $business;
    }

    public function index($exerciceId)
    {
        return $this->business->heuresExercice($exerciceId);
    }

    public function store(Request $request, $exerciceId)
    {
        $data = $request->validate([
            'montant' => 'numeric|require',
            'type' => 'integer|require',
            'sapeur_id' => 'integer|exists:sapeurs,id|require',
            'heure_exercice_type_id' => 'integer|exists:heure_exercice_types,id',
        ]);
        $heure = $this->business->ajouterHeureExercice($exerciceId, $data);
        return response()->json(['data' => $heure]);
    }

    public function update(Request $request, $exerciceId, $id)
    {
        $data = $request->validate([
            'montant' => 'numeric',
        ]);
        $heure = $this->business->modifierHeureExercice($exerciceId, $id, $data);
        return response()->json(['data' => $heure]);
    }

    public function destroy($exerciceId, $id)
    {
        $heure = $this->business->supprimerHeureExercice($exerciceId, $id);
        return response()->json(['data' => $heure]);
    }
}
