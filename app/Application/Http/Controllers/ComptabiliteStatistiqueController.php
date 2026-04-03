<?php

namespace App\Application\Http\Controllers;

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
        $materiels = DB::select("SELECT e.module, count(e.id) AS nb, sum(CASE
                    WHEN c.produit THEN -e.total
                    ELSE e.total
                END) AS total
                FROM ecritures AS e
                INNER JOIN comptes AS c ON c.id = e.compte_id
                WHERE e.exercice_comptable_id = ?
                GROUP BY e.module
            ", [$exerciceComptableId]);

        return response()->json(['data' => $materiels]);
    }
}
