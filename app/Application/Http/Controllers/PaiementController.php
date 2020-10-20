<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\ComptabiliteService;

class PaiementController extends Controller
{

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    /**
     * creer les paiements
     * $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     * $taux_avs - taux avs payé par le sapeur
     * $taux_ac - taux ac payé par le sapeur
     * $deduction - true si les déduction doivent être faites sur ce paiement
     */
    public function creer($exerciceComptableId, $taux_avs, $taux_ac, $deduction)
    {
        $ecritures = $this->service->getAllEcrituresForExerciceComptableById($exerciceComptableId);
        $totaux = [];
        $taux = $taux_ac + $taux_avs;


        //faire les totaux par sapeurs
        foreach ($ecritures as $ecriture) {
            if (!array_key_exists($ecriture->sapeur_id, $totaux)) {
                $totaux[$ecriture->sapeur_id] = array(
                    "solde" => 0.0,
                    "indemnite" => 0.0,
                    "frais" => 0.0,
                    "amende" => 0.0,
                    "avs" => 0.0,
                    "total" => 0.0
                );
            }
            $totaux[$ecriture->sapeur_id]['solde'] += $ecriture->solde;
            $totaux[$ecriture->sapeur_id]['indemnite'] += $ecriture->indemnite;
            $totaux[$ecriture->sapeur_id]['frais'] += $ecriture->frais;
            //pas encore présent dans écriture
            //$totaux[$ecriture->sapeur_id]['amende_montant']+=$ecriture->amende->;
        }

        //déductions
        if ($deduction) {
            foreach ($totaux as $key => $total) {
                $solde_imposable = $total['solde'] - 5000.0 < 0.0 ? 0.0 : $total['solde'] - 5000.0;
                $total_imposable = $solde_imposable + $total['indemnite'];
                //TODO ou si sapeur fait la demande
                if ($total_imposable > 2300.0) {
                    $totaux[$key]['avs'] = $total_imposable * $taux;
                }
            }
        }

        //total à payer
        foreach($totaux as $key => $total){
            //FIXME vérifier si sapeur ok pour amende
            $totaux[$key]['total'] = $total['solde'] + $total['indemnite'] + $total['frais'] - $total['amende'] - $total['avs'];
        }

        return response()->json(['data' => $totaux]);

    }
}
