<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Domaine\Exceptions\InvalidActionException;
use App\Models\AvsParam;
use App\Models\Compte;
use App\Models\Decompte;
use App\Models\Ecriture;
use App\Models\ExerciceComptable;
use App\Models\Paiement;
use App\Models\Sapeur;
use App\Models\SisParam;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Z38\SwissPayment\BIC;
use Z38\SwissPayment\IBAN;
use Z38\SwissPayment\IID;
use Z38\SwissPayment\Message\CustomerCreditTransfer;
use Z38\SwissPayment\PaymentInformation\PaymentInformation;
use Z38\SwissPayment\StructuredPostalAddress;
use Z38\SwissPayment\TransactionInformation\BankCreditTransfer;
use Z38\SwissPayment\Money;
use mikehaertl\pdftk\Pdf;
use Z38\SwissPayment\Text;
use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Models\Exercice;
use App\Models\TypeUnite;
use Illuminate\Support\Facades\DB;

/**
 * PaiementBusiness
 */
class PaiementBusiness
{

    public static function controlerStatusExerciceComptable(int $exerciceComptableId)
    {
        ImputationBusiness::controlerStatusExerciceComptable($exerciceComptableId);
    }

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
    public static function creerDecompte($ecritures, $designation, $exerciceComptableId, $date, $deduction)
    {
        self::controlerStatusExerciceComptable($exerciceComptableId);

        return DB::transaction(fn() => self::creerDecompteInterne($ecritures, $designation, $exerciceComptableId, $date, $deduction));
    }

    private static function creerDecompteInterne($ecritures, $designation, $exerciceComptableId, $date, $deduction)
    {
        $avsParam = AvsParam::first();
        if ($avsParam === null) {
            throw new ArrayException(["message" => "Paramètres AVS non configurés"]);
        }

        $indexedCompte = Compte::all()->keyBy('id')->all();

        $decompte = new Decompte();
        $decompte->designation = $designation;
        $decompte->exercice_comptable_id = $exerciceComptableId;
        $decompte->deduction = $deduction;
        $decompte->date = $date;
        $decompte->avs_total = 0;
        $decompte->ac_total = 0;
        $decompte->a_payer_total = 0;
        $decompte->a_facturer_total = 0;
        $decompte->total = 0;
        $decompte->save();

        $totaux = [];
        // faire les totaux par sapeurs
        foreach ($ecritures as $ecriture) {
            // ne pas ajouter une écriture déja payé
            if ($ecriture->decompte_id === null) {
                if (!array_key_exists($ecriture->sapeur_id, $totaux)) {
                    $totaux[$ecriture->sapeur_id] = [
                        "solde_a_percevoir" => 0.0,
                        "indemnite_a_percevoir" => 0.0,
                        "frais_forfaitaire_a_percevoir" => 0.0,
                        "frais_effectif_a_percevoir" => 0.0,
                        "avs_ac_a_cotiser" => 0.0,
                        "solde_percue" => 0.0,
                        "indemnite_percue" => 0.0,
                        "avs_ac_cotise" => 0.0,
                        "autre" => 0.0,
                    ];
                }

                switch ($ecriture->type) {
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE:
                        $totaux[$ecriture->sapeur_id]['solde_a_percevoir'] += $ecriture->total;
                        break;
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE:
                        $totaux[$ecriture->sapeur_id]['indemnite_a_percevoir'] += $ecriture->total;
                        break;
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_FRAIS_FORFAITAIRE:
                        $totaux[$ecriture->sapeur_id]['frais_forfaitaire_a_percevoir'] += $ecriture->total;
                        break;
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_FRAIS_EFFECTIF:
                        $totaux[$ecriture->sapeur_id]['frais_effectif_a_percevoir'] += $ecriture->total;
                        break;
                    case ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_AUTRE:
                    default:
                        // Tous les montants sont positifs, produit/charges défini par le compte
                        $compte = $indexedCompte[$ecriture->compte_id];
                        $total = $ecriture->total;
                        if ($compte->produit) {
                            $total = -$total;
                        }
                        $totaux[$ecriture->sapeur_id]['autre'] += $total;
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
                        $totaux[$p->sapeur_id] = [
                            "solde_a_percevoir" => 0.0,
                            "indemnite_a_percevoir" => 0.0,
                            "frais_forfaitaire_a_percevoir" => 0.0,
                            "frais_effectif_a_percevoir" => 0.0,
                            "avs_ac_a_cotiser" => 0.0,
                            "solde_percue" => 0.0,
                            "indemnite_percue" => 0.0,
                            "avs_ac_cotise" => 0.0,
                            "autre" => 0.0,
                        ];
                    }
                    $totaux[$p->sapeur_id]['solde_percue'] += $p->solde;
                    $totaux[$p->sapeur_id]['indemnite_percue'] += $p->indemnite;
                    $totaux[$p->sapeur_id]['avs_ac_cotise'] += $p->avs_ac;
                }
            }

            // Calcul du taux d'imposition
            $tauxParitaireAvsAc = ($avsParam->taux_ac + $avsParam->taux_avs) / 2.0;

            // Sapeurs ayant demandé de cotiser à l'AVS/AC même sous la franchise
            $sapeursCotisationAvs = Sapeur::whereIn('id', array_keys($totaux))->pluck('cotisation_avs', 'id')->all();

            // Calcul des déductions AVS/AC sur la part imposable
            foreach ($totaux as $key => $total) {
                $solde_imposable = max($total['solde_a_percevoir'] + $total['solde_percue'] - $avsParam->franchise_imposition, 0.0);
                $total_imposable = $solde_imposable + $total['indemnite_a_percevoir'] + $total['indemnite_percue'];

                if ($total_imposable >= $avsParam->franchise_avs || ($sapeursCotisationAvs[$key] ?? false)) {
                    $avs = ImputationBusiness::arrondi_5_centimes(
                        ImputationBusiness::arrondi_5_centimes($total_imposable * ($avsParam->taux_avs / 2.0))
                        - (($total['avs_ac_cotise'] / $tauxParitaireAvsAc) * ($avsParam->taux_avs / 2.0))
                    );
                    $ac = ImputationBusiness::arrondi_5_centimes(
                        ImputationBusiness::arrondi_5_centimes($total_imposable * ($avsParam->taux_ac / 2.0))
                        - (($total['avs_ac_cotise'] / $tauxParitaireAvsAc) * ($avsParam->taux_ac / 2.0))
                    );
                    // Côtisation sociale complète (part employé + part employeur paritaire),
                    // alors que $avs/$ac ne représentent qu'une part (utilisée telle
                    // quelle comme retenue sur la solde du sapeur ci-dessous)
                    $decompte->avs_total += $avs * 2;
                    $decompte->ac_total += $ac * 2;
                    $totaux[$key]['avs_ac_a_cotiser'] = $avs + $ac;
                }
            }
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

        $now = Carbon::now();
        $paiements = [];
        $ecritureAvsAc = [];
        $ecritureAvsGlobale = [
            'tarif' => 0,
            'quantite' => 1,
            'total' => 0,

            'designation' => "$designation - Côtisations AVS/AI/APG - AC",
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

            'created_at' => $now,
            'updated_at' => $now,
        ];

        // Création paiements
        foreach ($totaux as $key => $total) {
            if (
                round($total['solde_a_percevoir'], 2) !== 0.0 ||
                round($total['indemnite_a_percevoir'], 2) !== 0.0 ||
                round($total['frais_forfaitaire_a_percevoir'], 2) !== 0.0 ||
                round($total['frais_effectif_a_percevoir'], 2) !== 0.0 ||
                round($total['avs_ac_a_cotiser'], 2) !== 0.0 ||
                round($total['autre'], 2) !== 0.0
            ) {
                $paiements[] = [
                    'decompte_id' => $decompte->id,
                    'solde' => $total['solde_a_percevoir'],
                    'indemnite' => $total['indemnite_a_percevoir'],
                    'frais_forfaitaire' => $total['frais_forfaitaire_a_percevoir'],
                    'frais_effectif' => $total['frais_effectif_a_percevoir'],
                    'avs_ac' => $total['avs_ac_a_cotiser'],
                    'autre' => $total['autre'],
                    'total' => $total['total_final'],
                    'sapeur_id' => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Maj décompte
                $decompte->total += $total['total_final'];
                if ($total['total_final'] > 0) {
                    $decompte->a_payer_total += $total['total_final'];
                } else {
                    $decompte->a_facturer_total += $total['total_final'];
                }

                // Génération écriture AVS/AC pour le sapeur
                if ($deduction && $total['avs_ac_a_cotiser'] > 0.0) {
                    $ecritureAvsGlobale['tarif'] += $total['avs_ac_a_cotiser'] * 2;
                    $ecritureAvsGlobale['total'] += $total['avs_ac_a_cotiser'] * 2;

                    $ecritureAvsAc[] = [
                        'tarif' => $total['avs_ac_a_cotiser'],
                        'quantite' => 1,
                        'total' => -$total['avs_ac_a_cotiser'],

                        'designation' => "$designation - Participation AVS/AI/APG - AC",
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

                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Génération écriture AVS/AC pour le décompte
        // $ecritureAvsGlobale['total'] est la côtisation sociale complète (part employé,
        // déjà déduite dans total_final, + part employeur) : $decompte->total
        // représente le coût total réel pour le SIS, donc on ajoute le montant
        // complet ici, pas seulement la part employé (d'où pas de /2.0).
        if ($deduction && round($ecritureAvsGlobale['total'], 2) !== 0.0) {
            $decompte->total += $ecritureAvsGlobale['total'];
            $ecritureAvsAc[] = $ecritureAvsGlobale;
        }
        $decompte->save();
        Ecriture::insert($ecritureAvsAc);
        Paiement::insert($paiements);

        return $decompte;
    }

    /**
     * Supprimer un décompte
     * 
     * @param int $decompteId id du décompte à supprimer
     */
    public static function supprimerDecompte(int $decompteId)
    {
        $decompte = Decompte::find($decompteId);
        if ($decompte === null) {
            throw new InvalidActionException([], "Décompte introuvable");
        }
        self::controlerStatusExerciceComptable($decompte->exercice_comptable_id);

        DB::transaction(function () use ($decompteId) {
            Ecriture::where('decompte_id', $decompteId)->where('type', ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_CHARGE_AVS_AC)->delete();
            Ecriture::where('decompte_id', $decompteId)->update(['decompte_id' => null]);
            Decompte::whereId($decompteId)->delete(); // Cascade delete des paiements
        });
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
    public static function iso20022PourDecompte($decompteId, $nom, $bic, $iban)
    {
        $decompte = Decompte::with('paiements.sapeur.localite')->find($decompteId);
        if ($decompte === null) {
            throw new InvalidActionException([], "Décompte introuvable");
        }

        $nomDebiteur = Text::sanitize($nom, 70);

        try {
            // PmtInfId : identifiant du groupe de paiement, unique dans le message (un seul groupe ici)
            $paiement = new PaymentInformation("paiement-001", $nomDebiteur, new BIC($bic), new IBAN($iban));
        } catch (Exception $e) {
            throw new InvalidActionException([], 'Veuillez vérifier les informations de paiement de votre SIS');
        }
        $paiement->setExecutionDate(Carbon::parse($decompte->date));

        $i = 0;
        foreach ($decompte->paiements as $p) {
            if ($p->total <= 0) {
                continue;
            }
            $sapeur = $p->sapeur;
            if ($sapeur->iban === '') {
                throw new ArrayException([], "Numéro IBAN manquant pour '$sapeur->nom $sapeur->prenom'");
            }
            try {
                $ibanSapeur = new IBAN($sapeur->iban);
                $paiement->addTransaction(new BankCreditTransfer(
                    "instr-$i",
                    "e2e-$i",
                    new Money\CHF((int) round(ImputationBusiness::arrondi_5_centimes($p->total) * 100)),
                    Text::sanitize("$sapeur->prenom $sapeur->nom", 70),
                    new StructuredPostalAddress(
                        $sapeur->rue === '' ? null : Text::sanitize($sapeur->rue, 70),
                        $sapeur->no_rue === '' ? null : Text::sanitize($sapeur->no_rue, 16),
                        Text::sanitize($sapeur->localite->npa, 16),
                        Text::sanitize($sapeur->localite->designation, 35)
                    ),
                    $ibanSapeur,
                    IID::fromIBAN($ibanSapeur)
                ));
                $i++;
            } catch (InvalidArgumentException $e) {
                throw new ArrayException(['error' => $e->getMessage(), 'type' => $e::class], "Informations de paiement pour '$sapeur->nom $sapeur->prenom' invalides : $sapeur->iban");
            }
        }

        // Un PmtInf sans transaction n'est pas conforme au schéma pain.001 (rejet banque)
        if ($i === 0) {
            throw new ArrayException([], "Aucun versement à effectuer dans ce décompte (aucun montant positif).");
        }

        $message = new CustomerCreditTransfer(
            'decompte-' . $decompteId . '-' . Carbon::now()->format('YmdHis'),
            $nomDebiteur,
            CustomerCreditTransfer::SPS_2022,
            "GestSIS",
            "2.0"
        );
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

    public static function certificatSalaire($exerciceComptableId, $affichageFrais = false)
    {
        // Calcul des totaux
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $sisParam = SisParam::with(['sapeur', 'localite'])->first();
        if ($sisParam === null) {
            throw new ArrayException([], "Paramètres global du SIS non configuré");
        }
        $avsParam = AvsParam::first();
        if ($avsParam === null) {
            throw new ArrayException([], "Paramètres de l'AVS non configuré");
        }

        $totaux = [];
        foreach (Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get() as $d) {
            foreach ($d->paiements as $p) {
                if (!array_key_exists($p->sapeur_id, $totaux)) {
                    $totaux[$p->sapeur_id] = [
                        "solde" => 0,
                        "indemnite" => 0,
                        "avs_ac" => 0,
                        "frais_effectif" => 0,
                        "frais_forfaitaire" => 0,
                    ];
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
            foreach (Sapeur::whereIn('id', array_keys($totaux))->with(['localite', 'civilite'])->orderBy('nom')->get() as $sapeur) {
                $path = self::creationPdf($sapeur, $exerciceComptable, $totaux[$sapeur->id], $affichageFrais, true, $sisParam, $avsParam);
                $merged->addFile($path);
            }

            // Création du pdf final
            $content = $merged->toString();

            if ($content === false) {
                Log::error("Certificat de salaire creation", [
                    "exception" => $merged->getError(),
                ]);
                throw new ArrayException(["message" => "Erreur lors de la génération du pdf"]);
            }

            $headers = [
                'Content-type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="certificats_salaire.pdf"',
            ];

            return Response::make($content, 200, $headers);
        } catch (Exception $e) {
            Log::error("Certificat de salaire creation", [
                "exception" => $e,
            ]);
            throw $e;
        } finally {
            // Suppression du dossier même si erreur php
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
    public static function certificatSalaireSapeur(int $exerciceComptableId, int $sapeurId, bool $affichageFrais = false)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $sisParam = SisParam::with(['sapeur', 'localite'])->first();
        if ($sisParam == null) {
            throw new ArrayException([], "Paramètres global du SIS non configuré");
        }
        $avsParam = AvsParam::first();
        if ($avsParam == null) {
            throw new ArrayException([], "Paramètres de l'AVS non configuré");
        }

        $total = self::totauxPaiementsSapeur($exerciceComptableId, $sapeurId);

        $sapeur = Sapeur::with(['localite', 'civilite'])->find($sapeurId);
        return self::creationPdf($sapeur, $exerciceComptable, $total, $affichageFrais, false, $sisParam, $avsParam);
    }

    /**
     * Totaux des paiements d'un sapeur pour un exercice comptable
     *
     * @return array{solde: float, indemnite: float, avs_ac: float, frais_effectif: float, frais_forfaitaire: float}
     */
    public static function totauxPaiementsSapeur(int $exerciceComptableId, int $sapeurId): array
    {
        $decomptes = Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get();
        if ($decomptes->isEmpty()) {
            throw new ArrayException([], "Impossible de générer le certificat de salaire, aucun décompte trouvé !");
        }

        $total = [
            'solde' => 0.0,
            'indemnite' => 0.0,
            'avs_ac' => 0.0,
            'frais_effectif' => 0.0,
            'frais_forfaitaire' => 0.0,
        ];
        foreach ($decomptes as $d) {
            foreach ($d->paiements as $p) {
                if ($p->sapeur_id === $sapeurId) {
                    $total['solde'] += $p->solde;
                    $total['indemnite'] += $p->indemnite;
                    $total['avs_ac'] += $p->avs_ac;
                    $total['frais_effectif'] += $p->frais_effectif;
                    $total['frais_forfaitaire'] += $p->frais_forfaitaire;
                }
            }
        }

        return $total;
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
    private static function creationPdf($sapeur, $exerciceComptable, $total, $affichageFrais, $enregistrement, $sisParam, $avsParam)
    {
        $localite = $sapeur->localite;
        $civilite = $sapeur->civilite;

        $fields = [
            "A" => "Ja",
            "C2" => $sapeur->no_avs,
            "D" => $exerciceComptable->annee,
            "E-von" => "01.01." . $exerciceComptable->annee,
            "E-bis" => "31.12." . $exerciceComptable->annee,
            'HAnrede' => $civilite->forme_politesse,
            "HName" => "$sapeur->nom $sapeur->prenom",
            "HAdresse" => "$sapeur->rue $sapeur->no_rue",
            "HPostfach" => "$localite->npa $localite->designation",
            "1" => round($total['solde'] + $total['indemnite']),
            // remplissage point 6 - indemnités
            // "6" => $total['indemnite'],
            "8" => round($total['solde'] + $total['indemnite']),
            "9" => round($total['avs_ac']),
            "11" => round($total['solde'] + $total['indemnite']) - round($total['avs_ac']),
            "15-1" => "Répartition:\tTâches essentielles\t" . round($total['solde']),
            "15-2" => "\t\t\tIndemnités\t\t\t" . round($total['indemnite']),
            "OrtDatum" => self::dateFr(),
            "Unterschrift10" => $sisParam->nom,
            "Unterschrift11" => "{$sisParam->sapeur->nom} {$sisParam->sapeur->prenom}",
            "Unterschrift12" => "$sisParam->rue $sisParam->numero",
            "Unterschrift13" => "{$sisParam->localite->npa} {$sisParam->localite->designation}",
            "Unterschrift14" => $sisParam->telephone,
        ];

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
            if ($result === false) {
                throw new ArrayException(['error' => $pdf->getError()], "Une erreur est survenue durant la génération du certificat de salaire.");
            }
            return $path;
        } else {
            $result = $pdf->fillForm($fields)
                ->needAppearances()
                ->toString();
            if ($result === false) {
                throw new ArrayException(['error' => $pdf->getError()], "Une erreur est survenue durant la génération du certificat de salaire.");
            }
            return $result;
        }
    }

    /**
     * retourne la date sous la forme jour mois année (ex 1 janvier 2000)
     *
     * @return string date
     */
    private static function dateFr()
    {
        $date = Carbon::now()->locale('fr_CH');
        return "$date->day $date->monthName $date->year";
    }

    public static function creerDecompteAnnuel($exerciceComptableId, $date, $designation, $selection, $sapeurIds)
    {
        $avsParam = AvsParam::first();
        if ($avsParam === null) {
            throw new InvalidActionException([], 'Erreur, paramètres AVS manquant, veuillez les compléter dans paramètres.');
        }

        $deduction = true;
        $modules = [];
        if ($selection['ecrituresExercice']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_EXERCICE;
        }
        if ($selection['ecrituresIntervention']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_INTERVENTION;
        }
        if ($selection['ecrituresCours']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_COURS;
        }
        if ($selection['ecrituresDivers']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_DIVERS;
        }
        if ($selection['ecrituresAnnuel']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL;
        }
        if ($selection['ecrituresAmende']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_AMENDE;
        }
        if ($selection['ecrituresTravail']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL;
        }

        $ecrituresRequest = Ecriture::whereNull('decompte_id')
            ->where('exercice_comptable_id', $exerciceComptableId)
            ->whereIn('module', $modules);

        if (count($sapeurIds) > 0) {
            $ecrituresRequest = $ecrituresRequest->whereIn('sapeur_id', $sapeurIds);
        }

        $ecritures = $ecrituresRequest->get();
        if ($ecritures->isEmpty()) {
            throw new ArrayException([], 'Aucune écriture disponible pour la création du décompte.');
        }

        return self::creerDecompte($ecritures, $designation, $exerciceComptableId, $date, $deduction);
    }

    public static function creerDecompteSapeur($exerciceComptableId, $sapeurId, $date)
    {
        $sapeur = Sapeur::find($sapeurId);
        $designation = "Decompte $sapeur->nom $sapeur->prenom";
        $deduction = true;
        $ecritures = Ecriture::where('exercice_comptable_id', $exerciceComptableId)
            ->where('sapeur_id', $sapeurId)
            ->get();
        return self::creerDecompte($ecritures, $designation, $exerciceComptableId, $date, $deduction);
    }

    public static function creerDecompteExercice($exerciceId, $date, $deduction)
    {
        $designation = 'Decompte exercice';
        $exerciceComptableId = Exercice::find($exerciceId)->exercice_comptable_id;
        $ecritures = Ecriture::where('exercice_id', $exerciceId)->get();
        return self::creerDecompte($ecritures, $designation, $exerciceComptableId, $date, $deduction);
    }

    public static function iso20022PourDecompteStream($decompteId)
    {
        $params = SisParam::first();
        $nom = $params->nom;
        $bic = $params->bic;
        $iban = $params->iban;
        
        $nomFichier = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", "-", Decompte::find($decompteId)->designation) . ".xml";
        $content = self::iso20022PourDecompte($decompteId, $nom, $bic, $iban);

        // TODO: Refactor est-ce que ce code ne devrait pas se trouver du cote du controller ?
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            $nomFichier
        );
    }

    public static function impressionDecompte($decompteId, $sisKey)
    {
        $decompte = Decompte::find($decompteId);
        $ecritures = Ecriture::where('decompte_id', $decompteId)->orderBy('date')->get();

        $sapeursMap = Sapeur::get(['id', 'nom', 'prenom'])
            ->mapWithKeys(fn($sapeur) => [$sapeur->id => "$sapeur->nom $sapeur->prenom"])
            ->all();

        $unitesMap = TypeUnite::all()->pluck('abreviation', 'id')->all();

        $logoPath = SisParamBusiness::getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::Decompte,
            ["decompte" => $decompte, "sapeurs" => $sapeursMap, "ecritures" => $ecritures, "unites" => $unitesMap],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'decompte.pdf'
        );
    }

    public static function impressionDecompteSapeur($decompteId, $sapeurId, string $sisKey)
    {
        $ecritures = DB::table('ecritures')
            ->where('ecritures.sapeur_id', $sapeurId)
            ->where('ecritures.decompte_id', $decompteId)
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->select(
                'ecritures.*',
                DB::raw('CONCAT(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'sapeurs.iban',
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('sapeur')
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.module', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        return self::printDecompteSapeur($decompteId, $ecritures, $sisKey, false);
    }

    public static function impressionDecompteParSapeur($decompteId, string $sisKey)
    {
        $ecritures = DB::table('ecritures')
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->where('ecritures.decompte_id', $decompteId)
            ->select(
                'ecritures.*',
                DB::raw('CONCAT(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'sapeurs.iban',
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('sapeur')
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.module', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        return self::printDecompteSapeur($decompteId, $ecritures, $sisKey, true);
    }

    public static function impressionResumePourSapeur(int $exerciceComptableId, int $sapeurId, string $sisKey)
    {
        $ecritures = DB::table('ecritures')
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->join('decomptes', 'ecritures.decompte_id', '=', 'decomptes.id')
            ->where('decomptes.exercice_comptable_id', $exerciceComptableId)
            ->where('sapeurs.id', $sapeurId)
            ->select(
                'ecritures.*',
                DB::raw('CONCAT(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'sapeurs.iban',
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.module', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        return self::printResumeSapeur($exerciceComptableId, $ecritures, $sisKey);
    }

    public static function impressionResumeParSapeur(int $exerciceComptableId, string $sisKey)
    {
        $ecritures = DB::table('ecritures')
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->join('decomptes', 'ecritures.decompte_id', '=', 'decomptes.id')
            ->where('decomptes.exercice_comptable_id', '=', $exerciceComptableId)
            ->select(
                'ecritures.*',
                DB::raw('CONCAT(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'sapeurs.iban',
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('sapeur')
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.module', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        return self::printResumeSapeur($exerciceComptableId, $ecritures, $sisKey);
    }

    private static function printResumeSapeur(int $exerciceComptableId, $ecritures, string $sisKey)
    {
        $decomptes = Decompte::with('paiements')->where('exercice_comptable_id', $exerciceComptableId)->get();
        $decomptesMap = $decomptes->keyBy('id')->all();

        $comptesMap = Compte::all()->keyBy('id')->all();

        $sapeursMap = Sapeur::get(['id', 'nom', 'prenom'])
            ->mapWithKeys(fn($sapeur) => [$sapeur->id => "$sapeur->nom $sapeur->prenom"])
            ->all();

        $logoPath = SisParamBusiness::getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::ResumeParSapeur,
            ["decomptes" => $decomptesMap, "sapeurs" => $sapeursMap, "ecritures" => $ecritures, "comptes" => $comptesMap],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'resume-par-sapeur.pdf'
        );
    }

    private static function printDecompteSapeur($decompteId, $ecritures, string $sisKey, bool $resume = false)
    {
        $decompte = Decompte::with('paiements')->find($decompteId);
        $decomptes = Decompte::where('exercice_comptable_id', $decompte->exercice_comptable_id)->get();
        $decomptesMap = $decomptes->keyBy('id')->all();

        $comptesMap = Compte::all()->keyBy('id')->all();

        $sapeursMap = Sapeur::get(['id', 'nom', 'prenom'])
            ->mapWithKeys(fn($sapeur) => [$sapeur->id => "$sapeur->nom $sapeur->prenom"])
            ->all();

        $logoPath = SisParamBusiness::getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::DecompteParSapeur,
            ["decompte" => $decompte, "decomptes" => $decomptesMap, "sapeurs" => $sapeursMap, "ecritures" => $ecritures, "comptes" => $comptesMap, 'resume' => $resume],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'decompte-par-sapeur.pdf'
        );
    }
}
