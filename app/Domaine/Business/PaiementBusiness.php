<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use FPDM;
use Illuminate\Support\Facades\Storage;
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
     * @param float $franchiseAvs montant imposable minimum pour l'avs
     * @param float $franchiseImposition montant minimum pour que la solde soit imposable
     * 
     * @return Decompte décompte créé
     */
    public function creerDecompte($ecritures, $designation, $exerciceComptableId, $date, $deduction)
    {
        $avsParam = AvsParam::first();

        $decompte = new Decompte();
        $decompte->designation = $designation;
        $decompte->exercice_comptable_id = $exerciceComptableId;
        $decompte->deduction = $deduction;
        $decompte->date = $date;
        $decompte->avs_total = 0;
        $decompte->ac_total = 0;
        $decompte->total = 0;
        $decompte->save();

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
                        "avs_total" => 0.0
                    );
                }
                $totaux[$ecriture->sapeur_id]['solde'] += $ecriture->solde;
                $totaux[$ecriture->sapeur_id]['indemnite'] += $ecriture->indemnite;
                $totaux[$ecriture->sapeur_id]['frais'] += $ecriture->frais;
                // pas encore présent dans écriture, mais n'y sera pas
                //$totaux[$ecriture->sapeur_id]['amende']+=$ecriture->amende;

                // TODO: Attention lors de l'ajout des amendes
                $decompte->total += $ecriture->total;

                // $ecriture->date_paiement = $date;
                $ecriture->decompte_id = $decompte->id;
                $ecriture->save();
            }
        }

        //déductions
        $tauxParitaire = ($avsParam->taux_ac + $avsParam->taux_avs) / 2.0;
        if ($deduction) {
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
                            "avs_total" => 0.0
                        );
                    }
                    $totaux[$p->sapeur_id]['soldeTotal'] += $p->solde;
                    $totaux[$p->sapeur_id]['indemniteTotal'] += $p->indemnite;
                    $totaux[$p->sapeur_id]['avs_total'] += $p->avs;
                }
            }
            //TODO: fetch sapeurs déduction choix

            foreach ($totaux as $key => $total) {
                $solde_imposable = max($total['solde'] + $total['soldeTotal'] - $avsParam->franchise_imposition, 0.0);
                $total_imposable = $solde_imposable + $total['indemnite'] + $total['indemniteTotal'];

                // TODO: ou si sapeur en fait la demande
                if ($total_imposable >= $avsParam->franchise_avs) {
                    $totaux[$key]['avs'] = ($total_imposable * $tauxParitaire) - $total['avs_total'];
                    $decompte->avs_total += ($total_imposable * ($avsParam->taux_avs / 2.0)) - (($total['avs_total'] / $tauxParitaire) * ($avsParam->taux_avs / 2.0));
                    $decompte->ac_total += ($total_imposable * ($avsParam->taux_ac / 2.0)) - (($total['avs_total'] / $tauxParitaire) * ($avsParam->taux_ac / 2.0));
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
        if ($deduction) {
            Ecriture::insert([
                'solde' => 0,
                'indemnite' => 0,
                'frais' => 0,
                'avs' => true,
                'type_unite_id' => null,
                'designation' => $designation,
                'total' => $decompte->avs_total + $decompte->ac_total,
                'tarif' => 0,
                'quantite' => 1,
                'solde_min' => null,
                'solde_min_pour' => null,
                'sapeur_id' => null,
                'compte_id' => $avsParam->compte_id,
                'exercice_comptable_id' => $exerciceComptableId,
                'ecriture_categorie_id' => $avsParam->ecriture_categorie_id,
                'intervention_id' => null,
                'date' => $date,
                'heure' => "00:00:00",
                'decompte_id' => $decompte->id,
            ]);
        }
        Paiement::insert($paiements);

        return $decompte;
    }

    /**
     * Supprimer un décompte
     * 
     * @param int $decompteId id du décompte à supprimer
     * 
     * @return string booléen du résultat
     */
    public function supprimerDecompte($decompteId)
    {
        Ecriture::where('decompte_id', '=', $decompteId)->where('avs', '=', true)->delete();
        Ecriture::where('decompte_id', '=', $decompteId)->update(['decompte_id' => null]);
        Decompte::where('id', '=', $decompteId)->delete(); // Cascade delete des paiements
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
    public function iso20022PourDecompte($decompteId, $nom, $bic, $iban)
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
    public function iso20022PourPaiement($paiementId, $nom, $bic, $iban)
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
     * @param bool $affichageFrais true si affichage des frais
     * 
     * @return pdf certificats de salaire
     */

    public function certificatSalaire($exerciceComptableId, $affichageFrais = false)
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
                        "deduction" => 0,
                        "frais" => 0
                    );
                }
                $totaux[$p->sapeur_id]['solde'] += $p->solde;
                $totaux[$p->sapeur_id]['indemnite'] += $p->indemnite;
                $totaux[$p->sapeur_id]['deduction'] += $p->avs;
                $totaux[$p->sapeur_id]['frais'] += $p->frais;
            }
        }
        //emplacement temporaire pour les fichiers
        Storage::makeDirectory("tmp/" . $exerciceComptableId);
        //utilise https://github.com/mikehaertl/php-pdftk
        $merged = new Pdf();
        try {
            //génération du pdf de chaque sapeur
            foreach (Sapeur::whereIn('id', array_keys($totaux))->with(['localite', 'civilite'])->get() as $sapeur) {
                $path = $this->creationPdf($sapeur, $exerciceComptable, $totaux[$sapeur->id], $affichageFrais, true);
                $merged->addFile($path);
            }

            //création du pdf final
            $merged->send();
        } finally {
            //supression du dossier même si erreur php
            Storage::deleteDirectory("tmp/" . $exerciceComptableId);
        }
    }

    /**
     * Créer un pdf contenant le certificat de salaire d'un sapeur pour l'exercice comptable spécifié
     * 
     * @param int $exerciceComptableId id de l'exercice compable souhaité
     * @param int $sapeurId id du sapeur dont on veut le certificat de salaire
     * @param bool $affichageFrais true si affichage des frais
     * 
     * @return pdf certificat de salaire
     */
    public function certificatSalaireSapeur($exerciceComptableId, $sapeurId, $affichageFrais = false)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

        //Calcul des totaux
        $total['solde'] = 0;
        $total['indemnite'] = 0;
        $total['deduction'] = 0;
        $total['frais'] = 0;

        foreach (Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get() as $d) {
            foreach ($d->paiements as $p) {
                if ($p->sapeur_id == $sapeurId) {
                    $total['solde'] += $p->solde;
                    $total['indemnite'] += $p->indemnite;
                    $total['deduction'] += $p->avs;
                    $total['frais'] += $p->frais;
                }
            }
        }

        $sapeur = Sapeur::with(['localite', 'civilite'])->find($sapeurId);
        $this->creationPdf($sapeur, $exerciceComptable, $total, $affichageFrais, false);
    }

    /**
     * Création du pdf
     * 
     * @param int $sapeurId id du sapeur
     * @param ExerciceComptable $exerciceComptable exercice compatble en cours
     * @param array $total tableau contenant les totaux de solde, indeminté, frais et déduction
     * @param bool $affichageFrais true si les frais doivent apparaitre
     * @param bool $enregistrement true si le fichier doit 'etre enregistré, sortie navigateur sinon
     */
    private function creationPdf($sapeur, $exerciceComptable, $total, $affichageFrais, $enregistrement)
    {
        $localite = $sapeur->localite;
        $civilite = $sapeur->civilite;

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
            //rempliassage point 6 - indemnités
            //"6" => $total['indemnite'],
            "8" => $total['solde'] + $total['indemnite'],
            "9" => round($total['deduction']),
            "11" => ($total['solde'] + $total['indemnite']) - round($total['deduction']),
            "15-1" => "Répartition:\tSolde\t\t" . $total['solde'],
            "15-2" => "\t\t\tIndemnité\t" . $total['indemnite'],
            "OrtDatum" => $this->dateFr()
        );

        if ($total['frais'] > 0 && $affichageFrais) {
            $fields["13-2-3-2"] = $total['frais'];
        }

        $pdf = new FPDM(resource_path('certificatSalaire.pdf'));
        $pdf->useCheckboxParser = true;
        $pdf->load($fields, true);
        $pdf->merge();
        if ($enregistrement) {
            $path = Storage::path("tmp/" . $exerciceComptable->id . "/" . $sapeur->id . ".pdf");
            $pdf->Output("F", $path);
            return $path;
        } else {
            $pdf->Output();
        }
    }

    /**
     * retourne la date sous la forme jour mois année (ex 1 janvier 2000)
     * 
     * @return string date
     */
    private function dateFr()
    {
        $date = Carbon::now()->locale('fr_CH');
        return $date->day . " " . $date->monthName . " " . $date->year;
    }
}
