<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\ComptabiliteService;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Paiement;

class DecompteController extends Controller
{

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    /**
     * creer un décompte
     * $designation - nom du décompte
     * $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     * $taux_avs - taux avs payé par le sapeur
     * $taux_ac - taux ac payé par le sapeur
     * $deduction - true si les déduction doivent être faites sur ce paiement
     */
    public function creer($request)
    {

        $data = $request->validate([
            'designation' => 'string',
            'taux_avs' => 'numeric|min:0|max:1|nullable',
            'taux_ac' => 'numeric|min:0|max:1|nullable',
            'deduction' => 'boolean',
            'exerciceContableId' => 'integer|min:1'
        ]);

        $decompte = new Decompte();
        $decompte->designation = $data['designation'];
        $decompte->exercice_comptable_id = $data['exerciceComptableId'];
        $decompte->save();

        $ecritures = $this->service->getAllEcrituresForExerciceComptableById($data['exerciceComptableId']);
        $totaux = [];
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
                        "soldePI" => 0.0,
                        "indemintePI" => 0.0
                    );
                }
                $totaux[$ecriture->sapeur_id]['solde'] += $ecriture->solde;
                $totaux[$ecriture->sapeur_id]['indemnite'] += $ecriture->indemnite;
                $totaux[$ecriture->sapeur_id]['frais'] += $ecriture->frais;
                //pas encore présent dans écriture
                //$totaux[$ecriture->sapeur_id]['amende']+=$ecriture->amende;

                $ecriture->date_paiement = date('Y-m-d');
                $ecriture->decompte_id = $decompte->id;
            }
        }

        //déductions
        if ($data['deduction']) {
            $taux = $data['taux_ac'] + $data['taux_avs'];
            //vérifie si autre décompte sans déduction
            if (sizeof(Decompte::where('exercice_comptable_id', $data['exerciceComptableId'])->where('deduction', false)->get()) > 0) {
                foreach (Decompte::where('exercice_comptable_id', $data['exerciceComptableId'])->where('deduction', false)->get() as $d) {
                    foreach (Paiement::where('decompte_id', $d->id)->get() as $p) {
                        if (!array_key_exists($ecriture->sapeur_id, $totaux)) {
                            $totaux[$ecriture->sapeur_id] = array(
                                "solde" => 0.0,
                                "indemnite" => 0.0,
                                "frais" => 0.0,
                                "amende" => 0.0,
                                "avs" => 0.0,
                                "total" => 0.0,
                                "soldePI" => 0.0,
                                "indemintePI" => 0.0
                            );
                        }
                        $totaux['soldePI'] += $p->solde;
                        $totaux['indemintePI'] += $p->indemine;
                    }
                }
            }
            foreach ($totaux as $key => $total) {
                $solde_imposable = $total['solde'] + $total['soldePI'] - 5000.0 < 0.0 ? 0.0 : $total['solde'] + $total['soldePI'] - 5000.0;
                $total_imposable = $solde_imposable + $total['indemnite'] + $total['indemnitePI'];
                //TODO ou si sapeur fait la demande
                if ($total_imposable > 2300.0) {
                    $totaux[$key]['avs'] = $total_imposable * $taux;
                }
            }
        }

        //total à payer
        foreach ($totaux as $key => $total) {
            $totaux[$key]['total'] = $total['solde'] + $total['indemnite'] + $total['frais'] - $total['avs'];
        }

        //création paiements
        foreach ($totaux as $key => $total) {
            $paiement = new Paiement();
            $paiement->decompte_id = $decompte->id;
            $paiement->solde = $total['solde'];
            $paiement->indeminte = $total['solde'];
            $paiement->frais = $total['solde'];
            $paiement->amende = $total['solde'];
            $paiement->avs = $total['solde'];
            $paiement->total = $total['solde'];
            $paiement->sapeur_id = $key;
            $paiement->save();
        }

        return response()->json(['data' => $decompte]);
    }

    /**
     * Retourne tous les décomptes
     */
    public function getAll()
    {
        $decomptes = Decompte::all();

        return response()->json(['data' => $decomptes]);
    }

    /**
     * Retourne un décompte
     * $id - id du décompte souhaité
     */
    public function get($id)
    {
        $decomptes = Decompte::find($id);

        return response()->json(['data' => $decomptes]);
    }

    /**
     * Retourne tous les décompte pour un exercice comptable
     * $id - id de l'exercice comptable
     */
    public function getByExerciceComptable($id)
    {
        $decomptes = Decompte::where('exercice_comptable_id', $id)->get();

        return response()->json(['data' => $decomptes]);
    }
}
