<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\AbsenceBusiness;
use App\Models\Absence;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(int $exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

        $absences = Absence::where([
            ['debut', '<', $exerciceComptable->fin],
            ['fin', '>', $exerciceComptable->debut]
        ])->get();

        return response()->json(['data' => $absences]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sapeur_id' => 'integer|min:1',
            'debut' => 'date',
            'fin' => 'date',
        ]);

        $absence = AbsenceBusiness::ajouterAbsence($data);
        return response()->json(['data' => $absence]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'sapeur_id' => 'integer|min:1',
            'debut' => 'date',
            'fin' => 'date',
        ]);

        $absence = AbsenceBusiness::modifierAbsence($id, $data);
        return response()->json(['data' => $absence]);
    }

    public function destroy($id)
    {
        AbsenceBusiness::supprimerAbsence($id);
        return response()->json(['data' => 'ok']);
    }
}
