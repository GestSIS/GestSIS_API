<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\CoursSapeur;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;

class CoursSapeurController extends Controller
{
    public function index(Request $request, $exerciceComptableId)
    {
        // Check si permission comptabilite
        $permissions = $request->attributes->get('permissions', []);

        $avecEcritures = in_array('comptabilite.lecture', $permissions);

        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        if ($exerciceComptable == null) {
            return response()->json(['data' => []]);
        }

        $data = CoursSapeur::with($avecEcritures ? ['cours', 'ecritures'] : [])->where([
            ['date', '>=', $exerciceComptable->debut],
            ['date', '<=', $exerciceComptable->fin],
        ])->orderBy('date')->get();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sapeur_ids' => 'required|array|min:1',
            'sapeur_ids.*' => 'distinct|integer|exists:sapeurs,id',
            'date' => 'required|date',
            'duree' => 'required|numeric|min:0',
            'localite_id' => 'integer|exists:localites,id',
            'cours_id' => 'required|integer|exists:cours,id',
            'fonction_id' => 'integer|nullable',
            'grade_id' => 'integer|nullable',
            'date_fonction' => 'bail|required_with:fonction_id|date|nullable',
            'date_grade' => 'bail|required_with:grade_id|date|nullable',
        ]);

        $sapeurIds = $data['sapeur_ids'];
        unset($data['sapeur_ids']);

        $cours = SapeurBusiness::addCoursMultiple($sapeurIds, $data);
        return response()->json(['data' => $cours]);
    }
}
