<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\ExerciceComptable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SapeurStatistiqueController extends Controller
{
    public function civilite(int $exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $data = DB::select("SELECT c.id as 'civilite_id', count(DISTINCT s.id) as nb
                FROM civilites as c
                INNER JOIN sapeurs as s ON s.civilite_id = c.id
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE m.incorporation < ?
                AND (m.sortie IS NULL OR m.sortie > ?)
                AND s.type = 0
                GROUP BY c.id
            ", [$exerciceComptable->fin, $exerciceComptable->debut]);
        return response()->json(['data' => $data]);
    }

    public function fonction($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $data = DB::select("SELECT f.id as 'fonction_id', count(DISTINCT s.id) as nb
                FROM fonctions as f
                INNER JOIN fonction_sapeur as fs ON fs.fonction_id = f.id
                INNER JOIN sapeurs as s ON s.id = fs.sapeur_id
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE fs.debut <= ?
                AND (fs.fin IS NULL OR fs.fin >= ?)
                AND m.incorporation <= ?
                AND (m.sortie IS NULL OR m.sortie >= ?)
                AND s.type = 0
                GROUP BY f.id
            ", [Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->debut), Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->debut)]);
        return response()->json(['data' => $data]);
    }

    public function grade($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $materiels = DB::select("SELECT g.id as 'grade_id', count(DISTINCT gs.sapeur_id) as nb
                FROM grades as g
                INNER JOIN (
                    SELECT ROW_NUMBER() OVER (PARTITION BY gs.sapeur_id ORDER BY g2.tri DESC) 'row_number', gs.date as 'date', gs.sapeur_id as 'sapeur_id', gs.grade_id as 'grade_id'
                    FROM grade_sapeur as gs
                    INNER JOIN grades as g2 ON g2.id = gs.grade_id
                    WHERE gs.date <= ?
                ) as gs ON gs.grade_id = g.id
                INNER JOIN sapeurs as s ON s.id = gs.sapeur_id
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE gs.row_number = 1
                AND m.incorporation <= ?
                AND (m.sortie IS NULL OR m.sortie >= ?)
                AND s.type = 0
                GROUP BY g.id
            ", [Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->debut)]);
        return response()->json(['data' => $materiels]);
    }

    public function permis($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $materiels = DB::select("SELECT pt.id as 'permis_type_id', count(DISTINCT s.id) as nb
                FROM permis_types as pt
                INNER JOIN permis as p ON p.permis_type_id = pt.id
                INNER JOIN sapeurs as s ON s.id = p.sapeur_id
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE p.date <= ?
                AND m.incorporation <= ?
                AND (m.sortie IS NULL OR m.sortie >= ?)
                AND s.type = 0
                GROUP BY pt.id
            ", [Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->debut)]);
        return response()->json(['data' => $materiels]);
    }

    public function localite($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $data = DB::select("SELECT s.localite_id AS localite_id, count(DISTINCT s.id) as nb
                FROM sapeurs as s
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE m.incorporation < ?
                AND (m.sortie IS NULL OR m.sortie > ?)
                AND s.type = 0
                GROUP BY s.localite_id
            ", [$exerciceComptable->fin, $exerciceComptable->debut]);
        return response()->json(['data' => $data]);
    }
}
