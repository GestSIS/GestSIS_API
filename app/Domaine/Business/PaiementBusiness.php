<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Paiement;

class PaiementBusiness
{
    /**
     * creer un décompte
     * $designation - nom du décompte
     * $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     * $tauxAvs - taux avs payé par le sapeur
     * $tauxAc - taux ac payé par le sapeur
     * $deduction - true si les déduction doivent être faites sur ce paiement
     * $minimumImposableAVSAC - montant imposable minimum pour l'avs
     * $minimumSoldeImposable - montant minimum pour que la solde soit imposable
     */
    public static function creerDecompte($designation, $exerciceComptableId, $deduction, $tauxAc, $tauxAvs, $minimumSoldeImposable, $minimumImposableAVSAC)
    {

        $decompte = new Decompte();
        $decompte->designation = $designation;
        $decompte->exercice_comptable_id = $exerciceComptableId;
        $decompte->deduction = $deduction;
        $decompte->avsTotal = 0;
        $decompte->acTotal = 0;
        $decompte->save();

        $ecritures = Ecriture::where('exercice_comptable_id', $exerciceComptableId)->get();

        $totaux = array();
        //faire les totaux par sapeurs
        foreach ($ecritures as $ecriture) {
            //ne pas ajouter une écriture déja payé
            if ($ecriture->decompte_id == null) {
                if (!array_key_exists($ecriture->sapeur_id, $totaux)) {
                    $totaux[$ecriture->sapeur_id] = array(
                        "solde" => 0.0,
                        "indemnite" => 0.0,
                        "frais" => 0.0,
                        "amende" => 0.0,
                        "avs" => 0.0,
                        "total" => 0.0,
                        "soldeTotal" => 0.0,
                        "indemniteTotal" => 0.0,
                        "avsTotal" => 0.0
                    );
                }
                $totaux[$ecriture->sapeur_id]['solde'] += $ecriture->solde;
                $totaux[$ecriture->sapeur_id]['indemnite'] += $ecriture->indemnite;
                $totaux[$ecriture->sapeur_id]['frais'] += $ecriture->frais;
                //pas encore présent dans écriture
                //$totaux[$ecriture->sapeur_id]['amende']+=$ecriture->amende;

                $ecriture->date_paiement = date('Y-m-d');
                $ecriture->decompte_id = $decompte->id;
                $ecriture->save();

            }
        }

        //déductions
        if ($deduction) {
            $taux = $tauxAc + $tauxAvs;
            //vérifie si autre décompte sans déduction
            foreach (Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get() as $d) {
                foreach ($d->paiements as $p) {
                    if (!array_key_exists($p->sapeur_id, $totaux)) {
                        $totaux[$p->sapeur_id] = array(
                            "solde" => 0.0,
                            "indemnite" => 0.0,
                            "frais" => 0.0,
                            "amende" => 0.0,
                            "avs" => 0.0,
                            "total" => 0.0,
                            "soldeTotal" => 0.0,
                            "indemniteTotal" => 0.0,
                            "avsTotal" => 0.0
                        );
                    }
                    $totaux[$p->sapeur_id]['soldeTotal'] += $p->solde;
                    $totaux[$p->sapeur_id]['indemniteTotal'] += $p->indemine;
                    $totaux[$p->sapeur_id]['avsTotal'] += $p->avs;
                }
            }
            foreach ($totaux as $key => $total) {
                $solde_imposable = max($total['solde'] + $total['soldeTotal'] - $minimumSoldeImposable, 0.0);
                $total_imposable = $solde_imposable + $total['indemnite'] + $total['indemniteTotal'];
                
            
                //TODO ou si sapeur fait la demande
                if ($total_imposable > $minimumImposableAVSAC) {
                    $totaux[$key]['avs'] = ($total_imposable * $taux)-$total['avsTotal'];
                    $decompte->avsTotal += ($total_imposable * $tauxAvs)-(($total['avsTotal']/$taux)*$tauxAvs);
                    $decompte->acTotal += ($total_imposable * $tauxAc)-(($total['avsTotal']/$taux)*$tauxAc);
                }
            }
            $decompte->save();
        }

        //total à payer
        foreach ($totaux as $key => $total) {
            $totaux[$key]['total'] = $total['solde'] + $total['indemnite'] + $total['frais'] - $total['avs'];
        }

        $paiements = array();
        //création paiements
        foreach ($totaux as $key => $total) {
            $paiements[] = [
                'decompte_id' => $decompte->id,
                'solde' => $total['solde'],
                'indemnite' => $total['indemnite'],
                'frais' => $total['frais'],
                'amende' => $total['amende'],
                'avs' => $total['avs'],
                'total' => $total['total'],
                'sapeur_id' => $key
            ];
        }
        Paiement::insert($paiements);

        return $decompte;
    }
    
}
