<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ImputationBusiness;
use Illuminate\Support\Facades\DB;

class ComptabiliteStatistiqueController extends Controller
{
    public function compte(int $exerciceComptableId)
    {
        $data = DB::select("SELECT e.compte_id, count(e.id) AS nb, sum(e.total) AS total
                FROM ecritures AS e
                WHERE e.exercice_comptable_id = ?
                GROUP BY e.compte_id
            ", [$exerciceComptableId]);

        return response()->json(['data' => $data]);
    }

    public function categorie($exerciceComptableId)
    {
        $data = DB::select("SELECT e.ecriture_categorie_id, count(e.id) AS nb, sum(CASE
                    WHEN c.produit THEN -e.total
                    ELSE e.total
                END) AS total
                FROM ecritures AS e
                INNER JOIN comptes AS c ON c.id = e.compte_id
                WHERE e.exercice_comptable_id = ?
                GROUP BY e.ecriture_categorie_id
            ", [$exerciceComptableId]);

        return response()->json(['data' => $data]);
    }

    public function module($exerciceComptableId)
    {
        // Les fiches de travail (module 6) sont séparées selon l'événement lié, pour être rapportées
        // à leur domaine (exercice ou intervention)
        $materiels = DB::select("SELECT e.module,
                CASE
                    WHEN e.module = ? AND e.exercice_id IS NOT NULL THEN 'exercice'
                    WHEN e.module = ? AND e.intervention_id IS NOT NULL THEN 'intervention'
                END AS lien,
                count(e.id) AS nb, sum(CASE
                    WHEN c.produit THEN -e.total
                    ELSE e.total
                END) AS total
                FROM ecritures AS e
                INNER JOIN comptes AS c ON c.id = e.compte_id
                WHERE e.exercice_comptable_id = ?
                GROUP BY e.module, lien
            ", [ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL, ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL, $exerciceComptableId]);

        return response()->json(['data' => $materiels]);
    }
}
