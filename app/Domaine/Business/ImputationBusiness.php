<?php

namespace App\Domaine\Business;

use App\Domaine\SPI\EcritureRepository;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\FraisTypeRepository;
use App\Domaine\SPI\IndemniteTypeRepository;
use App\Domaine\SPI\InterventionRepository;
use App\Domaine\SPI\SapeurRepository;
use App\Domaine\Exceptions\ArrayException;
use Carbon\Carbon;

class ImputationBusiness
{
    protected $ecritureRepo;
    protected $indemniteRepo;
    protected $fraisRepo;
    protected $exerciceRepo;
    protected $interventionRepo;

    public function __construct(
        EcritureRepository $ecriture,
        SapeurRepository $sapeur,
        ExerciceRepository $exercice,
        InterventionRepository $intervention,
        IndemniteTypeRepository $indemnite,
        FraisTypeRepository $frais
    ) {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->sapeurRepo = $sapeur;
        $this->interventionRepo = $intervention;
        $this->indemniteRepo = $indemnite;
        $this->fraisRepo = $frais;
    }

    protected const UNITE_CHF_PAR_PIECE = 1;
    protected const UNITE_CHF_PAR_HEURE = 2;
    
    /**
     * Génères des frais annuels pour les sapeurs n'ayant pas encore de frais annuels
     */
    public function imputerAnnuel(int $exerciceComptableId)
    {
        // Choice available pour une seule imputation annuelle :
        // 1. ~~ Si déjà une imputation pour l'année alors ne rien faire~~
        // 2. OUI -> Ajouter des imputations uniquement pour les sapeurs qui n'ont pas de frais pour l'instant
        // 3. ~~ Tout supprimer pour l'année courante et tout regénérer~~
        //
        // Notes :
        // - Ne prend actuellement en compte que la fonction actuelle et non pas la date de l'entrée en vigeure de cette fonction
        // - Prend uniquement les actifs

        $indemnites = $this->indemniteRepo->listeIndemniteAnnuelType();
        $frais = $this->fraisRepo->listeFraisAnnuelType();

        $ecritures = $this->ecritureRepo->listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);

        $sapeurs = array_filter(
            $this->sapeurRepo->listeSapeurLight(),
            function ($s) use ($ecritures) {
                return $s->actif && count(array_filter($ecritures, function ($e) use ($s) {
                    return $e->sapeur_id === $s->id;
                })) == 0;
            }
        );

        // Génération des indemnités annuels
        foreach ($indemnites as $i) {
            array_map(
                function ($s) use ($i, $exerciceComptableId) {
                    $this->imputerIndemniteSapeur($i, $s, $exerciceComptableId);
                },
                array_filter($sapeurs, function ($s) use ($i) {
                    return $i->fonction_id === $s->fonction_id;
                })
            );
        }

        //Générations des frais annuels
        foreach ($frais as $f) {
            array_map(
                function ($s) use ($f, $exerciceComptableId) {
                    $this->imputerFraisSapeur($f, $s, $exerciceComptableId);
                },
                array_filter($sapeurs, function ($s) use ($f) {
                    return $f->fonction_id === $s->fonction_id;
                })
            );
        }
    }

    private function imputerIndemniteSapeur($indemniteType, $sapeur, $exerciceComptableId)
    {
        $total = $indemniteType->montant * $indemniteType->quantite;
        $ecriture = array(
            'solde' => 0,
            'indemnite' => $indemniteType->montant,
            'frais' => 0,
            'type_unite_id' => $indemniteType->type_unite_id,
            'designation' => $indemniteType->designation,
            'total' => $total,
            'tarif' => $indemniteType->montant,
            'quantite' => $indemniteType->quantite,
            'sapeur_id' => $sapeur->id,
            'compte_id' => $indemniteType->compte_id,
            'exercice_comptable_id' => $exerciceComptableId,
            'indemnite_annuel_type_id' => $indemniteType->id,
            'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id
        );

        $this->ecritureRepo->persisteNewEcriture($ecriture);
    }

    private function imputerFraisSapeur($fraisType, $sapeur, $exerciceComptableId)
    {
        $total = $fraisType->montant * $fraisType->quantite;
        $ecriture = array(
            'solde' => 0,
            'indemnite' => 0,
            'frais' => $fraisType->montant,
            'type_unite_id' => $fraisType->type_unite_id,
            'designation' => $fraisType->designation,
            'total' => $total,
            'tarif' => $fraisType->montant,
            'quantite' => $fraisType->quantite,
            'sapeur_id' => $sapeur->id,
            'compte_id' => $fraisType->compte_id,
            'exercice_comptable_id' => $exerciceComptableId,
            'frais_annuel_type_id' => $fraisType->id,
            'ecriture_categorie_id' => $fraisType->ecriture_categorie_id,
        );

        $this->ecritureRepo->persisteNewEcriture($ecriture);
    }

    public function imputerExercice($exerciceId, $data)
    {
        $exercice = $this->exerciceRepo->getExerciceByIdWith($exerciceId, ['sapeurs', 'localite']);

        if ($exercice->statut !== ExerciceBusiness::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException(array("message" => "Impossible d'imputer cet exercice"));
        }

        $indemniteType = $this->indemniteRepo->findIndemniteExerciceTypeById($data['indemnite_exercice_type_id']);

        $unite = $indemniteType->type_unite_id;
        $designation = "{$exercice->localite->designation} ({$exercice->lieu}) $exercice->designation";
        $sapeurs = array_filter($exercice->sapeurs, function ($sap) {
            return $sap->present;
        });
        if ($unite === self::UNITE_CHF_PAR_PIECE) {
            $this->imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation);
        } elseif ($unite === self::UNITE_CHF_PAR_HEURE && $indemniteType->par_fonction) {
            $this->imputerExerciceParHeureEtFonction($exercice, $sapeurs, $indemniteType, $designation);
        } elseif ($unite === self::UNITE_CHF_PAR_HEURE && $indemniteType->solde_min !== null) {
            $this->imputerExerciceParHeureEtSoldeMin($exercice, $sapeurs, $indemniteType, $designation);
        } else {
            dd("ERROR");
            return false;
            //TODO: WARNING IN LOGS -> Should never arrive here
        }

        // Ajout date imputation ??? -> NON car car l'imputation se fait lors d'une autre étape

        // Changer le statut de l'exercice
        return $this->exerciceRepo->updateExerciceById($exerciceId, ["statut" => ExerciceBusiness::EXERCICE_STATUT_IMPUTE])->statut;
    }

    public function imputerIntervention($interventionId, $data)
    {
        // $dateImputation = $data['date_imputation']; // TODO: Ajouter date d'imputation ?
        $indemniteType = $this->indemniteRepo->findIndemniteInterventionTypeById($data['indemnite_intervention_type_id']);
        $intervention = $this->interventionRepo->findWith($interventionId, ['presences', 'phases', 'localite', 'typeIntervention']);

        if ($intervention->statut !== InterventionBusiness::INTERVENTION_STATUT_VALIDE) {
            throw new ArrayException(array("message" => "Impossible d'imputer cette intervention"));
        }

        $unite = $indemniteType->type_unite_id;
        $designation = "{$intervention->localite->designation} ({$intervention->type->designation}) $intervention->lieu";

        //Grouper les présences par sapeurs
        $sapeurs = [];
        foreach ($intervention->presences as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            array_push($sapeurs[$presence->sapeur_id], $presence);
        }

        // $phases = $intervention->phases;

        // //TODO: Retourne les phases durant cette période
        // $getPhases = function ($presence) use ($phases) {
        // };

        foreach ($sapeurs as $sapeur_id => $presences) {
            $dureeNuit = 0;
            $debutNuit = null;
            $finNuit = null;

            if ($indemniteType->taux_nuit !== null) {
                $debutNuit = Carbon::parse($indemniteType->debut);
                $finNuit = Carbon::parse($indemniteType->fin);

                if (!($finNuit > $debutNuit)) {
                    $finNuit->addDays(1);
                }
                $dureeNuit += $debutNuit->floatDiffInHours($finNuit);
            }

            //Durées calculées en heures
            $dureeTarifStandard = 0;
            $dureeTarifWeekend = 0;
            $dureeTarifNuit = 0;

            //TODO: Adapt soldeTarif selon la fonction principale
            // ou pas
            $soldeTarif = $indemniteType->solde;
            $tauxWeekend = $indemniteType->taux_weekend;
            $tauxNuit = $indemniteType->taux_nuit;

            $testWeekend = $tauxWeekend !== null;
            $testNuit = $tauxNuit !== null;

            foreach ($presences as $presence) {
                $debut = Carbon::parse($presence->debut);
                $fin = Carbon::parse($presence->fin);
                $duree = $debut->floatDiffInHours($fin);

                if (!$testWeekend && !$testNuit) {
                    //Pas de taux
                    $dureeTarifStandard += $duree;
                } else {

                    //Arrondir debut à la fin de la première journée
                    //Arrondir fin à la fin de l'avant dernière journée
                    $debutCarbon = $debut->copy()->ceilDay();
                    $finCarbon = $fin->copy()->floorDay();

                    $nbWeekend = 0;
                    $nbWeek = 0;
                    // dd($debutCarbon);
                    if ($debutCarbon < $finCarbon) {
                        $nbWeekend += $debutCarbon->diffInDaysFiltered(function (Carbon $date) {
                            return $date->isWeekend();
                        }, $finCarbon);
                        $nbWeek += $debutCarbon->diffInDaysFiltered(function (Carbon $date) {
                            return !$date->isWeekend();
                        }, $finCarbon);
                    }
                    //Dispatch full days to hours
                    if ($testWeekend && $testNuit) {
                        $dureeTarifStandard += $nbWeek * $dureeNuit;
                        $dureeTarifWeekend += $nbWeekend * 24;
                        $dureeTarifNuit += $nbWeek * (24 - $dureeNuit);
                    } elseif ($testWeekend) {
                        $dureeTarifWeekend += $nbWeekend * 24;
                        $dureeTarifNuit += $nbWeek * 24;
                    } elseif ($testNuit) {
                        $dureeTarifStandard += ($nbWeek + $nbWeekend) * $dureeNuit;
                        $dureeTarifNuit += ($nbWeek + $nbWeekend) * (24 - $dureeNuit);
                    } else {
                        $dureeTarifStandard += ($nbWeek + $nbWeekend) * 24;
                    }

                    //Définition des deux périodes de nuit qui peuvent potentiellement overlap sur la présence
                    $diff = $debutNuit->diffInDays($debutCarbon, false);
                    $nightPeriodOneStart = $debutNuit->copy()->addDays($diff);
                    $nightPeriodOneEnd = $finNuit->copy()->addDays($diff);
                    $nightPeriodTwoStart = $nightPeriodOneStart->copy()->subDay();
                    $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->subDay();

                    if ($debutCarbon->copy()->subDay() >= $finCarbon) {
                        //Debut et fin la même journée
                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;
                            $overlapping += min($debut->max($nightPeriodOneStart)->floatDiffInHours($fin->min($nightPeriodOneEnd)), 0);
                            $overlapping += min($debut->max($nightPeriodTwoStart)->floatDiffInHours($fin->min($nightPeriodTwoEnd)), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }
                    } else {
                        //Two days

                        //Modification de la durée
                        $finJour = $debut->copy()->ceilDay();
                        $duree = $debut->floatDiffInHours($finJour);

                        //Premier jour de la présence -> début
                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            //Create period 1 start and end date
                            $overlapping += max($debut->max($nightPeriodOneStart)->floatDiffInHours($finJour->min($nightPeriodOneEnd), false), 0);
                            $overlapping += max($debut->max($nightPeriodTwoStart)->floatDiffInHours($finJour->min($nightPeriodTwoEnd), false), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }

                        //Deuxième jour de la présence -> fin

                        //Modification de la durée
                        $debutJour = $fin->copy()->floorDay();
                        $duree = $debutJour->floatDiffInHours($fin);

                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            $nightPeriodOneStart = $nightPeriodOneStart->copy()->addDays($nightPeriodOneStart->diffInDays($debutJour, false));
                            $nightPeriodOneEnd = $nightPeriodOneEnd->copy()->addDays($nightPeriodOneStart->diffInDays($debutJour, false));
                            $nightPeriodTwoStart = $nightPeriodOneStart->copy()->subDay(1);
                            $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->subDay(1);

                            $overlapping += max($debutJour->max($nightPeriodOneStart)->floatDiffInHours($fin->min($nightPeriodOneEnd), false), 0);
                            $overlapping += max($debutJour->max($nightPeriodTwoStart)->floatDiffInHours($fin->min($nightPeriodTwoEnd), false), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }
                    }
                }
            } //End boucle presences d'un sapeur

            $soldeStandard = $soldeTarif * $dureeTarifStandard;
            $soldeNuit = $soldeTarif * $dureeTarifNuit;
            $soldeWeekend = $soldeTarif * $dureeTarifWeekend;

            //Application des taux
            if ($testWeekend) {
                $soldeWeekend *= $tauxWeekend;
            }
            if ($testNuit) {
                $soldeNuit *= $tauxNuit;
            }

            //Génération des écritures
            if ($soldeStandard > 0) {
                $ecriture = array(
                    'solde' => $soldeStandard,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation,
                    'total' => $soldeStandard,
                    'tarif' => $soldeTarif,
                    'quantite' => $dureeTarifStandard,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }

            if ($soldeNuit > 0) {
                $ecriture = array(
                    'solde' => $soldeNuit,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation . " - Nuit",
                    'total' => $soldeNuit,
                    'tarif' => $soldeTarif,
                    'quantite' => $dureeTarifNuit,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'taux' => $tauxNuit,
                    'taux_description' => 'Nuit',
                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }

            if ($soldeWeekend > 0) {
                $ecriture = array(
                    'solde' => $soldeWeekend,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation . " - Weekend",
                    'total' => $soldeWeekend,
                    'tarif' => $soldeTarif,
                    'quantite' => $dureeTarifWeekend,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'taux' => $tauxWeekend,
                    'taux_description' => 'Weekend',
                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }
        }

        //TODO: Ajout date imputation

        // Update statut
        return $this->interventionRepo->editInterventionInformationsById($interventionId, [
            "statut" => InterventionBusiness::INTERVENTION_STATUT_IMPUTE
        ])->statut;
    }

    private function imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation)
    {
        // TODO: : solde_min should be null

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            $id = $this->sapeurRepo->getSapeurDetailsById($sapeur->sapeur_id)->fonction_id;

            $fonction_tarif = array_filter($indemniteType->fonctions, function ($f) use ($id) {
                return $f->fonction_id === $id;
            });

            $solde = 0;
            $indemnite = 0;
            if (count($fonction_tarif) > 0) {
                $tarif = array_pop($fonction_tarif);
                $solde += $tarif[0]->solde;
                $indemnite += $tarif[0]->indemnite;
            } else {
                $solde += $indemniteType->solde;
                $indemnite += $indemniteType->indemnite;
            }

            //Par pièce et pas par fonction -> pas de calcul
            $ecriture = array(
                'solde' => $solde,
                'indemnite' => $indemnite,
                'frais' => 0,
                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'total' => $solde + $indemnite,
                'tarif' => $solde + $indemnite,
                'quantite' => 1,
                'sapeur_id' => $sapeur->sapeur_id,
                'compte_id' => $indemniteType->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,
            );

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }

    private function imputerExerciceParHeureEtFonction($exercice, $sapeurs, $indemniteType, $designation)
    {
        //TODO: solde_min should be null
        //En minutes
        $duree = $exercice->duree / 60;

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            $id = $this->sapeurRepo->getSapeurDetailsById($sapeur->sapeur_id)->fonction_id;

            $fonction_tarif = array_filter($indemniteType->fonctions, function ($f) use ($id) {
                return $f->fonction_id === $id;
            });

            $soldeTarif = 0;
            $indemniteTarif = 0;
            if (count($fonction_tarif) > 0) {
                $tarif = array_pop($fonction_tarif);
                $soldeTarif = $tarif->solde;
                $indemniteTarif = $tarif->indemnite;
            } else {
                $soldeTarif = $indemniteType->solde;
                $indemniteTarif = $indemniteType->indemnite;
            }

            $solde = $soldeTarif * $duree;
            $indemnite = $indemniteTarif * $duree;

            //Par heure -> calcul de la durée
            $ecriture = array(
                'solde' => $solde,
                'indemnite' => $indemnite,
                'frais' => 0,
                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'total' => $solde + $indemnite,
                'tarif' => $soldeTarif + $indemniteTarif,
                'quantite' => $exercice->duree / 60,
                'solde_min' => $indemniteType->solde_min,
                'solde_min_pour' => $indemniteType->solde_min_pour,
                'sapeur_id' => $sapeur->sapeur_id,
                'compte_id' => $indemniteType->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,
            );

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }

    private function imputerExerciceParHeureEtSoldeMin($exercice, $sapeurs, $indemniteType, $designation)
    {
        //En minutes
        $duree = $exercice->duree / 60;

        $solde = 0;
        if ($duree > $indemniteType->solde_min_pour) {
            $solde += $indemniteType->solde_min;
            $duree -= $indemniteType->solde_min_pour;
        }

        $solde += $indemniteType->solde * $duree;

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            //Par heure -> calcul de la durée
            $ecriture = array(
                'solde' => $solde,
                'indemnite' => 0,
                'frais' => 0,
                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'total' => $solde,
                'tarif' => $indemniteType->solde,
                'quantite' => $exercice->duree / 60,
                'solde_min' => $indemniteType->solde_min,
                'solde_min_pour' => $indemniteType->solde_min_pour,
                'sapeur_id' => $sapeur->sapeur_id,
                'compte_id' => $indemniteType->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,
            );

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }
}
