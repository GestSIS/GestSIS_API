<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Compte;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
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
        if (is_null($avsParam)) {
            throw new ArrayException(array("message" => "Paramètres AVS non configurés"));
        }

        $comptes = Compte::all();
        $indexedCompte = [];
        foreach ($comptes as $compte) {
            $indexedCompte[$compte->id] = $compte;
        }

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
        // faire les totaux par sapeurs
        foreach ($ecritures as $ecriture) {
            // ne pas ajouter une écriture déja payé
            if ($ecriture->decompte_id == null) {
                if (!array_key_exists($ecriture->sapeur_id, $totaux)) {
                    $totaux[$ecriture->sapeur_id] = array(
                        "solde_a_percevoir" => 0.0,
                        "indemnite_a_percevoir" => 0.0,
                        "frais_forfaitaire_a_percevoir" => 0.0,
                        "frais_effectif_a_percevoir" => 0.0,
                        "avs_ac_a_cotiser" => 0.0,
                        "solde_percue" => 0.0,
                        "indemnite_percue" => 0.0,
                        "avs_ac_cotise" => 0.0,
                        "autre" => 0.0,
                    );
                }

                switch ($ecriture->type) {
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE:
                        $totaux[$ecriture->sapeur_id]['solde_a_percevoir'] += $ecriture->total;
                        $decompte->total += $ecriture->total;
                        break;
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE:
                        $totaux[$ecriture->sapeur_id]['indemnite_a_percevoir'] += $ecriture->total;
                        $decompte->total += $ecriture->total;
                        break;
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_FRAIS_FORFAITAIRE:
                        $totaux[$ecriture->sapeur_id]['frais_forfaitaire_a_percevoir'] += $ecriture->total;
                        $decompte->total += $ecriture->total;
                        break;
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_FRAIS_EFFECTIF:
                        $totaux[$ecriture->sapeur_id]['frais_effectif_a_percevoir'] += $ecriture->total;
                        $decompte->total += $ecriture->total;
                        break;
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_AUTRE:
                        // Tous les montants sont positifs, produit/charges défini par le compte
                        $compte = $indexedCompte[$ecriture->compte_id];
                        $total = $ecriture->total;
                        if ($compte->produit) {
                            $total = -$total;
                        }
                        $totaux[$ecriture->sapeur_id]['autre'] += $total;
                        $decompte->total += $total;
                        break;
                }

                // $ecriture->date_paiement = $date;
                $ecriture->decompte_id = $decompte->id;
                $ecriture->save();
            }
        }

        // Déductions
        if ($deduction) {
            // Chargement des anciens décomptes pour tenir compte des montants déjà perçus
            foreach (Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get() as $d) {
                foreach ($d->paiements as $p) {
                    if (!array_key_exists($p->sapeur_id, $totaux)) {
                        $totaux[$p->sapeur_id] = array(
                            "solde_a_percevoir" => 0.0,
                            "indemnite_a_percevoir" => 0.0,
                            "frais_forfaitaire_a_percevoir" => 0.0,
                            "frais_effectif_a_percevoir" => 0.0,
                            "avs_ac_a_cotiser" => 0.0,
                            "solde_percue" => 0.0,
                            "indemnite_percue" => 0.0,
                            "avs_ac_cotise" => 0.0,
                            "autre" => 0.0,
                        );
                    }
                    $totaux[$p->sapeur_id]['solde_percue'] += $p->solde;
                    $totaux[$p->sapeur_id]['indemnite_percue'] += $p->indemnite;
                    $totaux[$p->sapeur_id]['avs_ac_cotise'] += $p->avs_ac;
                }
            }

            // Calcul du taux d'imposition
            $tauxParitaireAvsAc = ($avsParam->taux_ac + $avsParam->taux_avs) / 2.0;

            // Calcul des déducations AVS/AC sur la part imposable
            foreach ($totaux as $key => $total) {
                $solde_imposable = max($total['solde_a_percevoir'] + $total['solde_percue'] - $avsParam->franchise_imposition, 0.0);
                $total_imposable = $solde_imposable + $total['indemnite_a_percevoir'] + $total['indemnite_percue'];

                // TODO: ou si sapeur en fait la demande
                if ($total_imposable >= $avsParam->franchise_avs) {
                    $avs = ImputationBusiness::arrondi_5_centimes($total_imposable * ($avsParam->taux_avs / 2.0)) - (($total['avs_ac_cotise'] / $tauxParitaireAvsAc) * ($avsParam->taux_avs / 2.0));
                    $ac = ImputationBusiness::arrondi_5_centimes($total_imposable * ($avsParam->taux_ac / 2.0)) - (($total['avs_ac_cotise'] / $tauxParitaireAvsAc) * ($avsParam->taux_ac / 2.0));
                    $decompte->avs_total += $avs;
                    $decompte->ac_total += $ac;
                    $totaux[$key]['avs_ac_a_cotiser'] = $avs + $ac;
                }
            }
            $decompte->save();
        }

        // Calcul du total à payer pour chaque sapeur
        foreach ($totaux as $key => $total) {
            $totaux[$key]['total_final'] =
                $total['solde_a_percevoir'] +
                $total['indemnite_a_percevoir'] +
                $total['frais_forfaitaire_a_percevoir'] +
                $total['frais_effectif_a_percevoir'] -
                $total['avs_ac_a_cotiser'] +
                $total['autre'];
        }

        $paiements = array();
        $ecritureAvsAc = array();
        $ecritureAvsGlobale = [
            'tarif' => 0,
            'quantite' => 1,
            'total' => 0,

            'designation' => $designation . " - Charges AVS/AI/APG - AC",
            'type_unite_id' => ImputationBusiness::UNITE_FORFAIT,
            'exercice_comptable_id' => $exerciceComptableId,
            'ecriture_categorie_id' => $avsParam->ecriture_categorie_id,
            'date' => $date,
            'heure' => "00:00:00",

            'decompte_id' => $decompte->id,
            'compte_id' => $avsParam->compte_id,
            'sapeur_id' => null,

            'module' => ImputationBusiness::ECRITURE_MODULE_AVS,
            'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_CHARGE_AVS_AC,
        ];

        // Création paiements
        foreach ($totaux as $key => $total) {
            $paiements[] = [
                'decompte_id' => $decompte->id,
                'solde' => $total['solde_a_percevoir'],
                'indemnite' => $total['indemnite_a_percevoir'],
                'frais_forfaitaire' => $total['frais_forfaitaire_a_percevoir'],
                'frais_effectif' => $total['frais_effectif_a_percevoir'],
                'avs_ac' => $total['avs_ac_a_cotiser'],
                'autre' => $total['autre'],
                'total' => $total['total_final'],
                'sapeur_id' => $key
            ];

            // Génération écriture AVS/AC pour le sapeur
            if ($deduction && $total['avs_ac_a_cotiser'] > 0) {
                $ecritureAvsGlobale['tarif'] += $total['avs_ac_a_cotiser'] * 2;
                $ecritureAvsGlobale['total'] += $total['avs_ac_a_cotiser'] * 2;

                $ecritureAvsAc[] = [
                    'tarif' => $total['avs_ac_a_cotiser'],
                    'quantite' => 1,
                    'total' => -$total['avs_ac_a_cotiser'],

                    'designation' => $designation . " - Participation AVS/AI/APG - AC",
                    'type_unite_id' => ImputationBusiness::UNITE_FORFAIT,
                    'exercice_comptable_id' => $exerciceComptableId,
                    'ecriture_categorie_id' => $avsParam->ecriture_categorie_id,
                    'date' => $date,
                    'heure' => "00:00:00",

                    'decompte_id' => $decompte->id,
                    'compte_id' => $avsParam->compte_id,
                    'sapeur_id' => $key,

                    'module' => ImputationBusiness::ECRITURE_MODULE_AVS,
                    'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_CHARGE_AVS_AC,
                ];
            }
        }

        // Génération écriture AVS/AC pour le décompze
        if ($deduction && $ecritureAvsGlobale['total'] != 0) {
            $ecritureAvsAc[] = $ecritureAvsGlobale;
        }
        Ecriture::insert($ecritureAvsAc);
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
        Ecriture::where('decompte_id', '=', $decompteId)->where('type', '=', ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_CHARGE_AVS_AC)->delete();
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
            if ($p->total > 0) {
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
        if ($p->total > 0) {
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
        }

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
        // Calcul des totaux
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $totaux = [];
        foreach (Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get() as $d) {
            foreach ($d->paiements as $p) {
                if (!array_key_exists($p->sapeur_id, $totaux)) {
                    $totaux[$p->sapeur_id] = array(
                        "solde" => 0,
                        "indemnite" => 0,
                        "avs_ac" => 0,
                        "frais_effectif" => 0,
                        "frais_forfaitaire" => 0,
                    );
                }
                $totaux[$p->sapeur_id]['solde'] += $p->solde;
                $totaux[$p->sapeur_id]['indemnite'] += $p->indemnite;
                $totaux[$p->sapeur_id]['avs_ac'] += $p->avs_ac;
                $totaux[$p->sapeur_id]['frais_effectif'] += $p->frais_effectif;
                $totaux[$p->sapeur_id]['frais_forfaitaire'] += $p->frais_forfaitaire;
            }
        }
        // Emplacement temporaire pour les fichiers
        Storage::makeDirectory("tmp/" . $exerciceComptableId);
        // Utilise https://github.com/mikehaertl/php-pdftk
        $merged = new Pdf(null, config('pdftk.config'));

        try {
            // Génération du pdf de chaque sapeur
            foreach (Sapeur::whereIn('id', array_keys($totaux))->with(['localite', 'civilite'])->get() as $sapeur) {
                $path = $this->creationPdf($sapeur, $exerciceComptable, $totaux[$sapeur->id], $affichageFrais, true);
                $merged->addFile($path);
            }

            // Création du pdf final
            $content = $merged->toString();

            if ($content === false) {
                Log::error("Certificat de salaire creation", [
                    "exception" => $merged->getError(),
                ]);
                throw new ArrayException(array("message" => "Erreur lors de la génération du pdf"));
            }

            $headers = [
                'Content-type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="certificats_salaire.pdf"',
            ];

            return Response::make($content, 200, $headers);
        } catch (Exception $e) {
            Log::error("Certificat de salaire creation", [
                "exception" => $e,
            ]);
        } finally {

            // Supression du dossier même si erreur php
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

        // Calcul des totaux
        $total['solde'] = 0;
        $total['indemnite'] = 0;
        $total['avs_ac'] = 0;
        $total['frais_effectif'] = 0;
        $total['frais_forfaitaire'] = 0;

        foreach (Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get() as $d) {
            foreach ($d->paiements as $p) {
                if ($p->sapeur_id == $sapeurId) {
                    $total['solde'] += $p->solde;
                    $total['indemnite'] += $p->indemnite;
                    $total['avs_ac'] += $p->avs_ac;
                    $total['frais_efectif'] += $p->frais_efectif;
                    $total['frais_forfaitaire'] += $p->frais_forfaitaire;
                }
            }
        }

        $sapeur = Sapeur::with(['localite', 'civilite'])->find($sapeurId);
        return $this->creationPdf($sapeur, $exerciceComptable, $total, $affichageFrais, false);
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
            "A" => "Ja",
            "C2" => $sapeur->no_avs,
            "D" => $exerciceComptable->annee,
            "E-von" => "01.01." . $exerciceComptable->annee,
            "E-bis" => "31.12." . $exerciceComptable->annee,
            'HAnrede' => $civilite->forme_politesse,
            "HName" => $sapeur->nom . " " . $sapeur->prenom,
            "HAdresse" => $sapeur->rue . " " . $sapeur->no_rue,
            "HPostfach" => $localite->npa . " " . $localite->designation,
            "1" => round($total['solde'] + $total['indemnite']),
            // remplissage point 6 - indemnités
            // "6" => $total['indemnite'],
            "8" => round($total['solde'] + $total['indemnite']),
            "9" => round($total['avs_ac']),
            "11" => round($total['solde'] + $total['indemnite']) - round($total['avs_ac']),
            "15-1" => "Répartition:\tSolde\t\t" . round($total['solde']),
            "15-2" => "\t\t\tIndemnité\t" . round($total['indemnite']),
            "OrtDatum" => $this->dateFr()
        );

        if ($affichageFrais) {
            $fields["13-1-2-2"] = round($total['frais_effectif']);
            $fields["13-2-3-2"] = round($total['frais_forfaitaire']);
        }

        $pdf = new Pdf(resource_path('certificatSalaire.pdf'), config('pdftk.config'));

        if ($enregistrement) {
            $path = Storage::path("tmp/" . $exerciceComptable->id . "/" . $sapeur->id . ".pdf");
            $result = $pdf->fillForm($fields)
                ->needAppearances()
                ->saveAs($path);
            // TODO: Check result
            return $path;
        } else {
            // TODO: Not supported for now
            throw new ArrayException([], "Not suported for now");
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
