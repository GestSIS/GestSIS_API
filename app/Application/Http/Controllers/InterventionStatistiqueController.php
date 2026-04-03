<?php

namespace App\Application\Http\Controllers;

use Illuminate\Support\Facades\DB;

class InterventionStatistiqueController extends Controller
{
    public function materiel(int $exerciceComptableId)
    {
        $data = DB::select("SELECT im.materiel_id, sum(im.quantite) as nb
                FROM intervention_materiel as im
                INNER JOIN interventions as i ON i.id = im.intervention_id
                WHERE i.exercice_comptable_id = ?
                GROUP BY im.materiel_id
            ", [$exerciceComptableId]);

        return response()->json(['data' => $data]);
    }

    public function vehicule($exerciceComptableId)
    {
        $data = DB::select("SELECT iv.vehicule_id, sum(i.stat_nb) as nb
                FROM intervention_vehicule as iv
                INNER JOIN interventions as i ON i.id = iv.intervention_id
                WHERE i.exercice_comptable_id = ?
                GROUP BY iv.vehicule_id
            ", [$exerciceComptableId]);

        return response()->json(['data' => $data]);
    }

    public function typeIntervention($exerciceComptableId)
    {
        $materiels = DB::select("SELECT t.id, COUNT(DISTINCT i.id) AS nb, SUM(TIMESTAMPDIFF(MINUTE, isa.debut, isa.fin) / 60) AS heures
                FROM type_interventions AS t
                INNER JOIN interventions AS i ON i.type_intervention_id = t.id
                LEFT OUTER JOIN intervention_sapeur AS isa ON i.id = isa.intervention_id
                WHERE i.exercice_comptable_id = ?
                GROUP BY t.id;
            ", [$exerciceComptableId]);

        return response()->json(['data' => $materiels]);
    }

    public function statFederal($exerciceComptableId)
    {
        $materiels = DB::select("SELECT s.id, COUNT(DISTINCT i.id) AS nb, SUM(TIMESTAMPDIFF(MINUTE, isa.debut, isa.fin) / 60) AS heures
                FROM stat_federals AS s
                INNER JOIN interventions AS i ON i.stat_federal_id = s.id
                LEFT OUTER JOIN intervention_sapeur AS isa ON i.id = isa.intervention_id
                WHERE i.exercice_comptable_id = ?
                GROUP BY s.id;
            ", [$exerciceComptableId]);

        return response()->json(['data' => $materiels]);
    }

    public function traitement($exerciceComptableId)
    {
        $materiels = DB::select("SELECT i.intervention_traitement_id AS id, COUNT(i.id) as nb
                FROM interventions as i
                WHERE i.exercice_comptable_id = ?
                GROUP BY i.intervention_traitement_id;
            ", [$exerciceComptableId]);

        return response()->json(['data' => $materiels]);
    }

    public function heuresParTrancheHoraire($exerciceComptableId)
    {
        // TODO: not yet implemented
        return response()->json(['data' => null]);
    }

    public function heuresParJour($exerciceComptableId)
    {
        $materiels = DB::select("SELECT i.intervention_traitement_id AS id, COUNT(i.id) as nb
                FROM interventions as i
                WHERE i.exercice_comptable_id = ?
                GROUP BY i.intervention_traitement_id;
            ", [$exerciceComptableId]);

        return response()->json(['data' => $materiels]);
    }

    public function heureParMois($exerciceComptableId)
    {
        $materiels = DB::select("SELECT i.intervention_traitement_id AS id, COUNT(i.id) as nb
                FROM interventions as i
                WHERE i.exercice_comptable_id = ?
                GROUP BY i.intervention_traitement_id;
            ", [$exerciceComptableId]);

        return response()->json(['data' => $materiels]);
    }
}
