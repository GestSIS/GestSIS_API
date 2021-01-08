<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\Civilite;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\ExerciceCategorie;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Sapeur;
use FPDM;
use Z38\SwissPayment\BIC;
use Z38\SwissPayment\IBAN;
use Z38\SwissPayment\IID;
use Z38\SwissPayment\Message\CustomerCreditTransfer;
use Z38\SwissPayment\PaymentInformation\PaymentInformation;
use Z38\SwissPayment\StructuredPostalAddress;
use Z38\SwissPayment\TransactionInformation\BankCreditTransfer;
use Z38\SwissPayment\Money;
use mikehaertl\pdftk\Pdf;

/**
 * PaiementBusiness
 */
class PaiementBusiness
{
    /**
     * creer un décompte
     * 
     * @param string $designation nom du décompte
     * @param int $exerciceComptableId id de l'exercice comptable pour lequel créer les paiements
     * @param float $taux_avs taux avs payé par le sapeur
     * @param float $taux_ac taux ac payé par le sapeur
     * @param boolean $deduction true si les déduction doivent être faites sur ce paiement
     * @param float $minimumImposableAVSAC montant imposable minimum pour l'avs
     * @param float $minimumSoldeImposable montant minimum pour que la solde soit imposable
     * 
     * @return Decompte décompte créé
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
                    $totaux[$key]['avs'] = ($total_imposable * $taux) - $total['avsTotal'];
                    $decompte->avsTotal += ($total_imposable * $tauxAvs) - (($total['avsTotal'] / $taux) * $tauxAvs);
                    $decompte->acTotal += ($total_imposable * $tauxAc) - (($total['avsTotal'] / $taux) * $tauxAc);
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

    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $decompteId id du décompte pour lequelle le fichier doit être créé
     * @param string $nom titulaire du compte débiteur
     * @param string $bic bic de la banque du compte débiteur
     * @param string $iban iban du compte débiteur
     * 
     * @return string fichier xml répondant à la norme ISO 20022
     */
    public static function iso20022FromDecompte($decompteId, $nom, $bic, $iban)
    {
        $paiements = Decompte::find($decompteId)->paiements()->get();
        $paiement = new PaymentInformation(
            "payment-000",
            $nom,
            new BIC($bic),
            new IBAN($iban)
        );
        $i = 0;
        foreach ($paiements as $p) {
            $sapeur = $p->sapeur()->get()[0];
            $transaction = new BankCreditTransfer(
                "instr-" . $i,
                "e2e-" . $i,
                new Money\CHF((int)($p->total * 100)),
                $sapeur->prenom . " " . $sapeur->nom,
                new StructuredPostalAddress($sapeur->rue == "" ? null : $sapeur->rue, $sapeur->no_rue == "" ? null : $sapeur->no_rue, $sapeur->localite()->get()[0]->npa, $sapeur->localite()->get()[0]->designation),
                new IBAN($sapeur->iban),
                IID::fromIBAN(new IBAN($sapeur->iban))
            );
            $paiement->addTransaction($transaction);
            $i++;
        }

        $message = new CustomerCreditTransfer('message-001', $nom);
        $message->addPayment($paiement);

        return $message->asXml();
    }

    /**
     * Créer un fichier iso20022 pour un paiement
     * 
     * @param int $paiementId id du paiement pour lequelle le fichier doit être créé
     * @param string $nom titulaire du compte débiteur
     * @param string $bic bic de la banque du compte débiteur
     * @param string $iban iban du compte débiteur
     * 
     * @return string fichier xml répondant à la norme ISO 20022
     */
    public static function iso20022FromPaiement($paiementId, $nom, $bic, $iban)
    {
        $paiement = new PaymentInformation(
            "payment-000",
            $nom,
            new BIC($bic),
            new IBAN($iban)
        );
        $p = Paiement::find($paiementId);
        $sapeur = $p->sapeur()->get()[0];
        $transaction = new BankCreditTransfer(
            "instr-001",
            "e2e-001",
            new Money\CHF((int)($p->total * 100)),
            $sapeur->prenom . " " . $sapeur->nom,
            new StructuredPostalAddress($sapeur->rue == "" ? null : $sapeur->rue, $sapeur->no_rue == "" ? null : $sapeur->no_rue, $sapeur->localite()->get()[0]->npa, $sapeur->localite()->get()[0]->designation),
            new IBAN($sapeur->iban),
            IID::fromIBAN(new IBAN($sapeur->iban))
        );
        $paiement->addTransaction($transaction);

        $message = new CustomerCreditTransfer('message-001', $nom);
        $message->addPayment($paiement);

        return $message->asXml();
    }

    /**
     * Créer un pdf contenant le certificat de salaire de tous les sapeurs pour l'exercice comptable spécifié
     * 
     * @param int $exerciceComptableId id de l'exercice compable souhaité
     * 
     * @return pdf certificats de salaire
     */

    public static function certificatSalaire($exerciceComptableId)
    {
        //calcul des totaux
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $totaux = [];
        foreach (Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get() as $d) {
            foreach ($d->paiements as $p) {
                if (!array_key_exists($p->sapeur_id, $totaux)) {
                    $totaux[$p->sapeur_id] = array(
                        "solde" => 0,
                        "indemnite" => 0,
                        "deduction" => 0
                    );
                }
                $totaux[$p->sapeur_id]['solde'] += $p->solde;
                $totaux[$p->sapeur_id]['indemnite'] += $p->indemnite;
                $totaux[$p->sapeur_id]['deduction'] += $p->avs;
            }
        }

        //génération du pdf de chaque sapeur
        mkdir("/app/storage/tpm/" . $exerciceComptableId, 0777, true);
        $merged = new Pdf();
        try {
            foreach ($totaux as $sapeurId => $total) {
                $sapeur = Sapeur::find($sapeurId);
                $localite = $sapeur->localite()->get()[0];
                $civilite = Civilite::find($sapeur->civilite_id);

                $fields = array(
                    'A' => "checked",
                    "C2" => $sapeur->no_avs,
                    "D" => $exerciceComptable->annee,
                    "E-von" => "01.01." . $exerciceComptable->annee,
                    "E-bis" => "31.12." . $exerciceComptable->annee,
                    'HAnrede' => $civilite->forme_politesse,
                    "HName" => $sapeur->nom . " " . $sapeur->prenom,
                    "HAdresse" => $sapeur->rue . " " . $sapeur->no_rue,
                    "HPostfach" => $localite->npa . " " . $localite->designation,
                    "1" => $total['solde'] + $total['indemnite'],
                    "8" => $total['solde'] + $total['indemnite'],
                    "9" => round($total['deduction']),
                    "11" => ($total['solde'] + $total['indemnite']) - round($total['deduction']),
                    "15-1" => "Répartition:\tSolde\t\t" . $total['solde'],
                    "15-2" => "\t\t\tIndemnité\t" . $total['indemnite'],
                    "OrtDatum" => PaiementBusiness::datefr()
                );
                $pdf = new FPDM('/app/resources/certificatSalaire.pdf');
                $pdf->useCheckboxParser = true;
                $pdf->flatten_mode = true;
                $pdf->load($fields, true);
                $pdf->merge();
                $pdf->Output("F", "/app/storage/tpm/" . $exerciceComptableId . "/" . $sapeurId . ".pdf");
                $merged->addFile("/app/storage/tpm/" . $exerciceComptableId . "/" . $sapeurId . ".pdf");
            }

            //output mergedPdf et nettoyage
            $merged->send();
        } finally {
            PaiementBusiness::delete_files("/app/storage/tpm/");
        }
    }

    /**
     * Créer un pdf contenant le certificat de salaire d'un sapeur pour l'exercice comptable spécifié
     * 
     * @param int $exerciceComptableId id de l'exercice compable souhaité
     * @param int $sapeurId id du sapeur dont on veut le certificat de salaire
     * 
     * @return pdf certificat de salaire
     */
    public static function certificatSalaireSapeur($exerciceComptableId, $sapeurId)
    {
        //infos de base
        $sapeur = Sapeur::find($sapeurId);
        $localite = $sapeur->localite()->get()[0];
        $civilite = Civilite::find($sapeur->civilite_id);
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

        //Calcul des totaux
        $solde = 0;
        $indemnite = 0;
        $deduction = 0;

        foreach (Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get() as $d) {
            foreach ($d->paiements as $p) {
                if ($p->sapeur_id == $sapeurId) {
                    $solde += $p->solde;
                    $indemnite += $p->indemnite;
                    $deduction += $p->avs;
                }
            }
        }

        //remplissage pdf
        $fields = array(
            'A' => "checked",
            "C2" => $sapeur->no_avs,
            "D" => $exerciceComptable->annee,
            "E-von" => "01.01." . $exerciceComptable->annee,
            "E-bis" => "31.12." . $exerciceComptable->annee,
            'HAnrede' => $civilite->forme_politesse,
            "HName" => $sapeur->nom . " " . $sapeur->prenom,
            "HAdresse" => $sapeur->rue . " " . $sapeur->no_rue,
            "HPostfach" => $localite->npa . " " . $localite->designation,
            "1" => $solde + $indemnite,
            "8" => $solde + $indemnite,
            "9" => round($deduction),
            "11" => ($solde + $indemnite) - round($deduction),
            "15-1" => "Répartition:\tSolde\t\t" . $solde,
            "15-2" => "\t\t\tIndeminté\t" . $indemnite,
            "OrtDatum" => PaiementBusiness::datefr()
        );
        $pdf = new FPDM('/app/resources/certificatSalaire.pdf');
        $pdf->useCheckboxParser = true;
        $pdf->load($fields, true);
        $pdf->merge();
        $pdf->Output();
    }

    /**
     * retourne la date sous la forme jour mois année (ex 1 janvier 2000)
     * 
     * @return string date
     */
    private static function datefr()
    {
        $mois = array(1 => " janvier ", " février ", " mars ", " avril ", " mai ", " juin ", " jullet ", " août ", " septembre ", " octobre ", " novembre ", " décembre ");
        return date('j') . $mois[date('n')] . date('Y');
    }

    /**
     * supprime la cible et tous ses enfants de manière recursive
     * 
     * @param string $target fichier/dossier cible
     */
    private static function delete_files($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file) {
                PaiementBusiness::delete_files($file);
            }

            rmdir($target);
        } elseif (is_file($target)) {
            unlink($target);
        }
    }
}
